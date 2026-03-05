<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Sync students from simas_db.siswa into cbt_db.students
     * Kelas is determined from the appropriate rombel_semester_N column
     * based on the student's angkatan_masuk and current date.
     */
    public function syncFromSimas(Request $request)
    {
        set_time_limit(300); // 5 minutes for large sync
        if ($request->isMethod('get')) {
            // Get stats from simas_db
            $simasStudents = DB::connection('simas')->table('siswa')
                ->select('angkatan_masuk', DB::raw('COUNT(*) as jumlah'))
                ->where('status_siswa', 'Aktif')
                ->groupBy('angkatan_masuk')
                ->orderBy('angkatan_masuk')
                ->get();

            $cbtTotal = Student::count();

            return view('admin.students.sync', compact('simasStudents', 'cbtTotal'));
        }

        // Determine which angkatan to sync
        $angkatanSelected = $request->input('angkatan', []);
        if (empty($angkatanSelected)) {
            return back()->with('error', 'Pilih minimal satu angkatan.');
        }

        // Current date for semester calculation
        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        $synced = 0;
        $updated = 0;

        foreach ($angkatanSelected as $angkatan) {
            // Calculate current semester for this angkatan
            // Semester 1 = Jul-Dec of angkatan year
            // Semester 2 = Jan-Jun of angkatan+1 year
            // Semester 3 = Jul-Dec of angkatan+1 year
            // etc.
            $yearsElapsed = $currentYear - $angkatan;
            if ($currentMonth >= 7) {
                $semester = ($yearsElapsed * 2) + 1;
            } else {
                $semester = $yearsElapsed * 2;
            }
            $semester = max(1, min(6, $semester));

            $rombelColumn = 'rombel_semester_' . $semester;

            $students = DB::connection('simas')->table('siswa')
                ->where('angkatan_masuk', $angkatan)
                ->where('status_siswa', 'Aktif')
                ->get();

            foreach ($students as $s) {
                $kelas = $s->$rombelColumn;

                // Fallback: use latest non-null rombel if current is null
                if (empty($kelas)) {
                    for ($i = $semester; $i >= 1; $i--) {
                        $col = 'rombel_semester_' . $i;
                        if (!empty($s->$col)) {
                            $kelas = $s->$col;
                            break;
                        }
                    }
                }

                $jk = null;
                if ($s->jk === 'Laki-laki') $jk = 'L';
                elseif ($s->jk === 'Perempuan') $jk = 'P';

                $existing = Student::where('nisn', $s->nisn)->first();
                if ($existing) {
                    $existing->update([
                        'nama' => $s->nama,
                        'kelas' => $kelas,
                        'jenis_kelamin' => $jk,
                        'agama' => $s->agama ?? null,
                        'is_active' => true,
                    ]);
                    $updated++;
                } else {
                    Student::create([
                        'nisn' => $s->nisn,
                        'nama' => $s->nama,
                        'kelas' => $kelas,
                        'jenis_kelamin' => $jk,
                        'agama' => $s->agama ?? null,
                        'password' => Hash::make($s->nisn),
                        'is_active' => true,
                    ]);
                    $synced++;
                }
            }
        }

        // Auto-sync students to exam activity groups based on rombel
        $groupsSynced = $this->autoSyncExamGroups();

        return redirect()->route('admin.students.index')
            ->with('success', "Sinkronisasi berhasil! $synced siswa baru ditambahkan, $updated siswa diperbarui. $groupsSynced kelompok ujian di-update.");
    }

    /**
     * Auto-sync students to rombel-based exam activity groups.
     * For each group whose nama_kelompok matches a student kelas,
     * re-sync the student list so new/moved students are included.
     */
    private function autoSyncExamGroups()
    {
        $groups = \App\Models\ExamActivityGroup::all();
        $kelasList = Student::where('is_active', true)->pluck('kelas')->unique()->toArray();
        $synced = 0;

        foreach ($groups as $group) {
            // Only auto-sync if group name matches a known kelas (rombel-based group)
            if (in_array($group->nama_kelompok, $kelasList)) {
                $studentIds = Student::where('is_active', true)
                    ->where('kelas', $group->nama_kelompok)
                    ->pluck('id');
                $group->students()->sync($studentIds);
                $synced++;
            }
        }

        return $synced;
    }
    public function index(Request $request)
    {
        $query = Student::query();
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nisn', 'like', "%{$request->search}%")
                  ->orWhere('kelas', 'like', "%{$request->search}%");
            });
        }
        if ($request->kelas) {
            $query->where('kelas', $request->kelas);
        }
        $students = $query->orderBy('nama')->paginate(20);
        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        return view('admin.students.index', compact('students', 'kelasList'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|unique:students,nisn',
            'nama' => 'required',
            'kelas' => 'nullable',
        ]);

        Student::create([
            'nisn' => $request->nisn,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'jenis_kelamin' => $request->jenis_kelamin,
            'password' => Hash::make($request->nisn), // default password = NISN
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $request->validate([
            'nisn' => 'required|unique:students,nisn,' . $id,
            'nama' => 'required',
        ]);

        $data = $request->only('nisn', 'nama', 'kelas', 'jenis_kelamin', 'is_active');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $student->update($data);

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function import(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('admin.students.import');
        }

        $request->validate(['file' => 'required|mimes:csv,txt']);
        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($rows);
        $count = 0;

        foreach ($rows as $row) {
            if (count($row) < 2) continue;
            Student::updateOrCreate(
                ['nisn' => trim($row[0])],
                [
                    'nama' => trim($row[1]),
                    'kelas' => trim($row[2] ?? ''),
                    'jenis_kelamin' => trim($row[3] ?? '') ?: null,
                    'password' => Hash::make(trim($row[0])),
                ]
            );
            $count++;
        }

        return redirect()->route('admin.students.index')->with('success', "$count siswa berhasil diimport.");
    }
}
