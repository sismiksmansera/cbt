<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\ExamSessionStudent;
use App\Models\Student;
use App\Models\AttendanceConfirmation;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function dashboard()
    {
        $teacherId = session('teacher_id');
        $source = session('teacher_source');
        $nip = session('teacher_nip');

        // Get activity IDs where this teacher is a supervisor
        $query = \App\Models\ExamActivitySupervisor::query();
        if ($source === 'external') {
            $query->where('id', $teacherId)->where('is_external', true);
        } else {
            $query->where('nip', $nip)->where('is_external', false);
        }
        $activityIds = $query->pluck('exam_activity_id')->unique()->toArray();

        $activeSessions = ExamSession::where('status', 'active')
            ->whereIn('exam_activity_id', $activityIds)
            ->with(['categories', 'sessionGroups.students'])
            ->orderBy('nama_sesi')
            ->get();

        return view('teacher.dashboard', compact('activeSessions'));
    }

    public function attendance($sessionId, Request $request)
    {
        $session = ExamSession::with('activity.groups.students')->findOrFail($sessionId);
        $teacherId = session('teacher_id');

        // Check if teacher already confirmed a kelas/group for this session
        $confirmedKelas = AttendanceConfirmation::where('exam_session_id', $sessionId)
            ->where('teacher_id', $teacherId)
            ->value('kelas');

        $lockedKelas = $confirmedKelas;
        $kelas = $confirmedKelas ?: $request->kelas;

        // Determine if session uses test groups
        $activity = $session->activity;
        $useGroups = $activity && $activity->groups->count() > 0;

        if ($useGroups) {
            $kelasList = $activity->groups->pluck('nama_kelompok')->values();
        } else {
            $kelasList = ExamSessionStudent::where('exam_session_id', $sessionId)
                ->join('students', 'students.id', '=', 'exam_session_students.student_id')
                ->distinct()
                ->pluck('students.kelas')
                ->sort()
                ->values();
        }

        $students = collect();
        $existingAttendance = collect();

        if ($kelas) {
            $sessionStudentIds = ExamSessionStudent::where('exam_session_id', $sessionId)
                ->pluck('student_id');

            if ($useGroups) {
                $group = $activity->groups->where('nama_kelompok', $kelas)->first();
                $groupStudentIds = $group ? $group->students->pluck('id') : collect();
                $students = Student::whereIn('id', $sessionStudentIds)
                    ->whereIn('id', $groupStudentIds)
                    ->orderBy('nama')
                    ->get();
            } else {
                $students = Student::whereIn('id', $sessionStudentIds)
                    ->where('kelas', $kelas)
                    ->orderBy('nama')
                    ->get();
            }

            $existingAttendance = AttendanceConfirmation::where('exam_session_id', $sessionId)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        return view('teacher.attendance', compact('session', 'kelasList', 'kelas', 'students', 'existingAttendance', 'lockedKelas'));
    }

    public function saveAttendance(Request $request, $sessionId)
    {
        $session = ExamSession::findOrFail($sessionId);
        $teacherId = session('teacher_id');
        $kelas = $request->kelas;

        foreach ($request->attendance ?? [] as $studentId => $status) {
            AttendanceConfirmation::updateOrCreate(
                [
                    'exam_session_id' => $sessionId,
                    'student_id' => $studentId,
                ],
                [
                    'teacher_id' => $teacherId,
                    'kelas' => $kelas,
                    'status' => $status,
                    'confirmed_at' => now(),
                ]
            );
        }

        return redirect()->route('teacher.monitor', ['sessionId' => $sessionId, 'kelas' => $kelas])
            ->with('success', 'Kehadiran siswa berhasil disimpan.');
    }

    public function monitor($sessionId, Request $request)
    {
        $session = ExamSession::with('activity.groups.students')->findOrFail($sessionId);
        $teacherId = session('teacher_id');

        // Check if teacher already confirmed a kelas/group
        $confirmedKelas = AttendanceConfirmation::where('exam_session_id', $sessionId)
            ->where('teacher_id', $teacherId)
            ->value('kelas');

        $lockedKelas = $confirmedKelas;
        $kelas = $confirmedKelas ?: $request->kelas;

        $activity = $session->activity;
        $useGroups = $activity && $activity->groups->count() > 0;

        if ($useGroups) {
            $kelasList = $activity->groups->pluck('nama_kelompok')->values();
        } else {
            $kelasList = ExamSessionStudent::where('exam_session_id', $sessionId)
                ->join('students', 'students.id', '=', 'exam_session_students.student_id')
                ->distinct()
                ->pluck('students.kelas')
                ->sort()
                ->values();
        }

        $sessionStudents = collect();
        $attendanceMap = collect();

        if ($kelas) {
            if ($useGroups) {
                $group = $activity->groups->where('nama_kelompok', $kelas)->first();
                $groupStudentIds = $group ? $group->students->pluck('id') : collect();
                $studentIds = $groupStudentIds;
            } else {
                $studentIds = Student::where('kelas', $kelas)->pluck('id');
            }

            $sessionStudents = ExamSessionStudent::where('exam_session_id', $sessionId)
                ->whereIn('student_id', $studentIds)
                ->with('student')
                ->get();

            $attendanceMap = AttendanceConfirmation::where('exam_session_id', $sessionId)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');
        }

        return view('teacher.monitor', compact('session', 'kelasList', 'kelas', 'sessionStudents', 'attendanceMap', 'lockedKelas'));
    }
}
