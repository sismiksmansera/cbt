<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ExamSession;
use App\Models\ExamSessionStudent;
use Illuminate\Http\Request;

class StudentLoginController extends Controller
{
    public function showLoginForm()
    {
        if (session('student_id') && session('exam_session_id')) {
            return redirect()->route('student.exam.confirm');
        }
        return view('auth.student-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nisn' => 'required',
            'token' => 'required',
        ]);

        $student = Student::where('nisn', $request->nisn)->where('is_active', true)->first();
        if (!$student) {
            return back()->withErrors(['login' => 'NISN tidak ditemukan atau tidak aktif.']);
        }

        $session = ExamSession::where('token', strtoupper($request->token))
                    ->where('status', 'active')
                    ->first();
        if (!$session) {
            return back()->withErrors(['login' => 'Token sesi tidak valid atau sesi belum aktif.']);
        }

        $pivot = ExamSessionStudent::where('exam_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->first();
        if (!$pivot) {
            return back()->withErrors(['login' => 'Anda tidak terdaftar dalam sesi ujian ini.']);
        }

        if ($pivot->status === 'selesai') {
            return back()->withErrors(['login' => 'Anda sudah menyelesaikan ujian ini.']);
        }

        // Check if student is locked
        if ($pivot->is_locked) {
            return back()->withErrors(['login' => 'Akun Anda terkunci karena terdeteksi melakukan aktivitas di luar ujian. Hubungi pengawas untuk membuka kunci.']);
        }

        // Check attendance confirmation
        $attendance = \App\Models\AttendanceConfirmation::where('exam_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();
        if ($attendance && $attendance->status === 'tidak_hadir') {
            return back()->withErrors(['login' => 'Anda tidak dapat mengikuti ujian karena tercatat tidak hadir oleh guru pengawas.']);
        }

        // Mark as started
        if ($pivot->status === 'belum_mulai') {
            $pivot->update([
                'status' => 'mengerjakan',
                'waktu_mulai' => now(),
            ]);
        }

        // Only clear cached questions on first login (preserve on re-login after unlock)
        if ($pivot->login_count <= 0) {
            session()->forget("exam_questions_{$session->id}_{$student->id}");
        }

        // Increment login count
        $pivot->increment('login_count');

        session([
            'student_id' => $student->id,
            'student_name' => $student->nama,
            'student_nisn' => $student->nisn,
            'exam_session_id' => $session->id,
        ]);

        return redirect()->route('student.exam.confirm');
    }

    public function logout()
    {
        session()->forget(['student_id', 'student_name', 'student_nisn', 'exam_session_id']);
        return redirect()->route('student.login');
    }
}
