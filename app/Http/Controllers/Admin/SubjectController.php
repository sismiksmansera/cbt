<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount('exams', 'questions')->orderBy('nama')->get();
        return view('admin.subjects.index', compact('subjects'));
    }

    public function syncFromSimas()
    {
        $mapelList = DB::connection('simas')->table('mata_pelajaran')
            ->select('nama_mapel')
            ->distinct()
            ->orderBy('nama_mapel')
            ->pluck('nama_mapel');

        $synced = 0;
        foreach ($mapelList as $nama) {
            $exists = Subject::where('nama', $nama)->first();
            if (!$exists) {
                Subject::create(['nama' => $nama]);
                $synced++;
            }
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', "Sinkronisasi selesai! $synced mata pelajaran baru ditambahkan dari SIMAS.");
    }

    public function store(Request $request)
    {
        $request->validate(['nama' => 'required']);
        Subject::create($request->only('nama', 'kode', 'deskripsi'));
        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $request->validate(['nama' => 'required']);
        $subject->update($request->only('nama', 'kode', 'deskripsi'));
        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
