<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherLoginController extends Controller
{
    public function showLoginForm()
    {
        if (session('teacher_id')) {
            return redirect()->route('teacher.dashboard');
        }
        return view('auth.teacher-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required',
        ]);

        // Try guru table first
        $guru = DB::connection('simas')->table('guru')
            ->where('nip', $request->nip)
            ->where('status', 'Aktif')
            ->first();

        if ($guru && Hash::check($request->password, $guru->password)) {
            session([
                'teacher_id' => $guru->id,
                'teacher_name' => $guru->nama,
                'teacher_nip' => $guru->nip,
                'teacher_source' => 'guru',
            ]);
            return redirect()->route('teacher.dashboard');
        }

        // Try guru_bk table
        $guruBk = DB::connection('simas')->table('guru_bk')
            ->where('nip', $request->nip)
            ->where('status', 'Aktif')
            ->first();

        if ($guruBk && Hash::check($request->password, $guruBk->password)) {
            session([
                'teacher_id' => $guruBk->id,
                'teacher_name' => $guruBk->nama,
                'teacher_nip' => $guruBk->nip,
                'teacher_source' => 'guru_bk',
            ]);
            return redirect()->route('teacher.dashboard');
        }

        // Try external supervisor (pengawas lain) with default password 123456
        $external = \App\Models\ExamActivitySupervisor::where('nip', $request->nip)
            ->where('is_external', true)
            ->first();

        if ($external && $request->password === '123456') {
            session([
                'teacher_id' => $external->id,
                'teacher_name' => $external->nama_pengawas,
                'teacher_nip' => $external->nip,
                'teacher_source' => 'external',
            ]);
            return redirect()->route('teacher.dashboard');
        }

        return back()->withErrors(['login' => 'NIP atau password salah.']);
    }

    public function logout()
    {
        session()->forget(['teacher_id', 'teacher_name', 'teacher_nip', 'teacher_source']);
        return redirect()->route('teacher.login');
    }
}
