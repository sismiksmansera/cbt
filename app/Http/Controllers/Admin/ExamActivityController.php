<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamActivity;
use App\Models\ExamActivitySupervisor;
use App\Models\ExamActivityGroup;
use App\Models\ExamSession;
use App\Models\ExamSessionStudent;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;

class ExamActivityController extends Controller
{
    public function index()
    {
        $activities = ExamActivity::with('supervisors')->orderBy('tanggal_pelaksanaan', 'desc')->get();
        return view('admin.exam-activities.index', compact('activities'));
    }

    public function create()
    {
        $teachers = Teacher::orderBy('nama')->get();
        $students = Student::where('is_active', true)->orderBy('kelas')->orderBy('nama')->get();
        return view('admin.exam-activities.create', compact('teachers', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required',
            'tanggal_pelaksanaan' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_pelaksanaan',
            'peserta_ujian' => 'required',
        ]);

        $activity = ExamActivity::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'tanggal_selesai' => $request->tanggal_selesai,
            'peserta_ujian' => $request->peserta_ujian,
            'kelompok_tes_mode' => $request->kelompok_tes_mode ?? 'tanpa',
        ]);

        // Save selected teachers as supervisors
        if ($request->teacher_ids) {
            foreach ($request->teacher_ids as $teacherId) {
                $teacher = Teacher::find($teacherId);
                if ($teacher) {
                    ExamActivitySupervisor::create([
                        'exam_activity_id' => $activity->id,
                        'teacher_id' => $teacher->id,
                        'nama_pengawas' => $teacher->nama,
                        'nip' => $teacher->nip,
                        'jenis_kelamin' => $teacher->jenis_kelamin,
                        'asal_instansi' => 'SMAN 1 Seputih Raman',
                        'is_external' => false,
                    ]);
                }
            }
        }

        // Save external supervisors
        if ($request->ext_nama) {
            foreach ($request->ext_nama as $i => $nama) {
                if (empty($nama)) continue;
                ExamActivitySupervisor::create([
                    'exam_activity_id' => $activity->id,
                    'teacher_id' => null,
                    'nama_pengawas' => $nama,
                    'nip' => $request->ext_nip[$i] ?? '',
                    'jenis_kelamin' => $request->ext_jk[$i] ?? 'L',
                    'asal_instansi' => $request->ext_instansi[$i] ?? '',
                    'is_external' => true,
                ]);
            }
        }

        // Save test groups
        if ($request->peserta_ujian === 'Siswa') {
            $this->saveTestGroups($request, $activity->id);
        }

        return redirect()->route('admin.exam-activities.index')
            ->with('success', 'Kegiatan ujian berhasil dibuat.');
    }

    public function edit($id)
    {
        $activity = ExamActivity::with(['supervisors', 'groups.students'])->findOrFail($id);
        $teachers = Teacher::orderBy('nama')->get();
        $students = Student::where('is_active', true)->orderBy('kelas')->orderBy('nama')->get();
        $selectedTeacherIds = $activity->supervisors->where('is_external', false)->pluck('teacher_id')->toArray();
        $externalSupervisors = $activity->supervisors->where('is_external', true)->values();
        return view('admin.exam-activities.edit', compact('activity', 'teachers', 'students', 'selectedTeacherIds', 'externalSupervisors'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required',
            'tanggal_pelaksanaan' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_pelaksanaan',
            'peserta_ujian' => 'required',
        ]);

        $activity = ExamActivity::findOrFail($id);
        $activity->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'tanggal_selesai' => $request->tanggal_selesai,
            'peserta_ujian' => $request->peserta_ujian,
            'kelompok_tes_mode' => $request->kelompok_tes_mode ?? 'tanpa',
        ]);

        // Clear old supervisors and re-save
        ExamActivitySupervisor::where('exam_activity_id', $id)->delete();

        if ($request->teacher_ids) {
            foreach ($request->teacher_ids as $teacherId) {
                $teacher = Teacher::find($teacherId);
                if ($teacher) {
                    ExamActivitySupervisor::create([
                        'exam_activity_id' => $id,
                        'teacher_id' => $teacher->id,
                        'nama_pengawas' => $teacher->nama,
                        'nip' => $teacher->nip,
                        'jenis_kelamin' => $teacher->jenis_kelamin,
                        'asal_instansi' => 'SMAN 1 Seputih Raman',
                        'is_external' => false,
                    ]);
                }
            }
        }

        if ($request->ext_nama) {
            foreach ($request->ext_nama as $i => $nama) {
                if (empty($nama)) continue;
                ExamActivitySupervisor::create([
                    'exam_activity_id' => $id,
                    'teacher_id' => null,
                    'nama_pengawas' => $nama,
                    'nip' => $request->ext_nip[$i] ?? '',
                    'jenis_kelamin' => $request->ext_jk[$i] ?? 'L',
                    'asal_instansi' => $request->ext_instansi[$i] ?? '',
                    'is_external' => true,
                ]);
            }
        }

        // === CAPTURE SESSION GROUP SELECTIONS BEFORE DELETING OLD GROUPS ===
        $sessions = ExamSession::where('exam_activity_id', $id)->get();
        $sessionGroupNames = [];
        foreach ($sessions as $session) {
            // Store the group names each session had selected (before old groups get deleted)
            $selectedNames = $session->sessionGroups()
                ->pluck('nama_kelompok')
                ->toArray();
            $sessionGroupNames[$session->id] = $selectedNames;
        }

        // Save test groups (deletes old, creates new)
        if ($request->peserta_ujian === 'Siswa') {
            $this->saveTestGroups($request, $id);
        }

        // === CASCADE: Remap session group selections by name ===
        $activity->load('groups.students');
        // Build name-to-new-group map
        $nameToGroup = [];
        foreach ($activity->groups as $grp) {
            $nameToGroup[$grp->nama_kelompok] = $grp;
        }

        foreach ($sessions as $session) {
            $oldSelectedNames = $sessionGroupNames[$session->id] ?? [];

            if (empty($oldSelectedNames)) {
                // Session had no groups selected — skip, don't auto-assign
                continue;
            }

            // Map old selected names to new group IDs
            $newSelectedIds = [];
            foreach ($oldSelectedNames as $name) {
                if (isset($nameToGroup[$name])) {
                    $newSelectedIds[] = $nameToGroup[$name]->id;
                }
            }

            // Re-sync session group links (only previously selected groups)
            $session->sessionGroups()->sync($newSelectedIds);

            // Collect students only from the session's selected groups
            $studentIds = $activity->groups
                ->whereIn('id', $newSelectedIds)
                ->flatMap(fn($g) => $g->students->pluck('id'))
                ->unique()
                ->toArray();

            // Refresh student list for this session
            ExamSessionStudent::where('exam_session_id', $session->id)
                ->whereNotIn('student_id', $studentIds)
                ->delete();

            foreach ($studentIds as $sid) {
                ExamSessionStudent::firstOrCreate([
                    'exam_session_id' => $session->id,
                    'student_id' => $sid,
                ]);
            }
        }

        return redirect()->route('admin.exam-activities.index')
            ->with('success', 'Kegiatan ujian berhasil diperbarui.');
    }

    private function saveTestGroups(Request $request, $activityId)
    {
        // Clear old groups
        ExamActivityGroup::where('exam_activity_id', $activityId)->delete();

        $mode = $request->kelompok_tes_mode ?? 'tanpa';

        if ($mode === 'rombel') {
            // Auto-create groups by student kelas
            $kelasList = Student::where('is_active', true)->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
            foreach ($kelasList as $kelas) {
                $group = ExamActivityGroup::create([
                    'exam_activity_id' => $activityId,
                    'nama_kelompok' => $kelas,
                ]);
                $studentIds = Student::where('is_active', true)->where('kelas', $kelas)->pluck('id');
                $group->students()->sync($studentIds);
            }
        } elseif ($mode === 'custom') {
            if ($request->group_names) {
                foreach ($request->group_names as $i => $name) {
                    if (empty($name)) continue;
                    $group = ExamActivityGroup::create([
                        'exam_activity_id' => $activityId,
                        'nama_kelompok' => $name,
                    ]);
                    $studentIds = $request->input("group_students.$i", []);
                    if (!empty($studentIds)) {
                        $group->students()->sync($studentIds);
                    }
                }
            }
        }
    }

    public function destroy($id)
    {
        ExamActivity::findOrFail($id)->delete();
        return redirect()->route('admin.exam-activities.index')
            ->with('success', 'Kegiatan ujian berhasil dihapus.');
    }
}
