<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('nama')->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    public function syncFromSimas()
    {
        $synced = 0;

        // Sync from guru table
        $guruList = DB::connection('simas')->table('guru')
            ->where('status', 'Aktif')
            ->get();

        foreach ($guruList as $guru) {
            Teacher::updateOrCreate(
                ['nip' => $guru->nip, 'sumber' => 'guru'],
                [
                    'nama' => $guru->nama,
                    'jenis_kelamin' => $guru->jenis_kelamin,
                    'jabatan' => $guru->jabatan ?? 'Guru Mapel',
                    'mapel_diampu' => $guru->mapel_diampu ?? '-',
                    'status' => $guru->status,
                ]
            );
            $synced++;
        }

        // Sync from guru_bk table
        $guruBkList = DB::connection('simas')->table('guru_bk')
            ->where('status', 'Aktif')
            ->get();

        foreach ($guruBkList as $guru) {
            Teacher::updateOrCreate(
                ['nip' => $guru->nip, 'sumber' => 'guru_bk'],
                [
                    'nama' => $guru->nama,
                    'jenis_kelamin' => $guru->jenis_kelamin,
                    'jabatan' => $guru->jabatan ?? 'Guru BK',
                    'mapel_diampu' => 'Bimbingan Konseling',
                    'status' => $guru->status,
                ]
            );
            $synced++;
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', "Berhasil sinkronisasi {$synced} data guru dari SIMAS.");
    }

    public function destroy($id)
    {
        Teacher::findOrFail($id)->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }
}
