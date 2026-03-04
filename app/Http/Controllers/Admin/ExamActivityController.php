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

    public function printCards($id)
    {
        $activity = ExamActivity::with(['groups.students' => function($q) {
            $q->orderBy('nama');
        }])->findOrFail($id);

        // Build flat list of cards: each student in each group
        $cards = [];
        foreach ($activity->groups as $group) {
            $nomor = 1;
            foreach ($group->students as $student) {
                $cards[] = [
                    'kelompok' => $group->nama_kelompok,
                    'nomor' => $nomor,
                    'nama' => $student->nama,
                    'nisn' => $student->nisn,
                ];
                $nomor++;
            }
        }

        return view('admin.exam-activities.print-cards', compact('activity', 'cards'));
    }

    public function destroy($id)
    {
        ExamActivity::findOrFail($id)->delete();
        return redirect()->route('admin.exam-activities.index')
            ->with('success', 'Kegiatan ujian berhasil dihapus.');
    }

    public function downloadPeserta($id)
    {
        $activity = ExamActivity::with(['groups.students'])->findOrFail($id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($activity->groups as $gIdx => $group) {
            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, mb_substr($group->nama_kelompok, 0, 31));
            $spreadsheet->addSheet($sheet, $gIdx);

            // Title
            $sheet->setCellValue('A1', $activity->nama_kegiatan);
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            $sheet->setCellValue('A2', 'Kelompok Tes: ' . $group->nama_kelompok);
            $sheet->mergeCells('A2:E2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

            // Header
            $row = 4;
            $headers = ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Jenis Kelamin'];
            foreach ($headers as $col => $header) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cell, $header);
            }
            $headerRange = 'A' . $row . ':E' . $row;
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Data
            $students = $group->students->sortBy('nama')->values();
            foreach ($students as $i => $student) {
                $row++;
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValueExplicit('B' . $row, $student->nisn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $student->nama);
                $sheet->setCellValue('D' . $row, $student->kelas);
                $sheet->setCellValue('E' . $row, $student->jenis_kelamin ?? '-');

                $dataRange = 'A' . $row . ':E' . $row;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            // Total
            $row++;
            $sheet->setCellValue('A' . $row, 'Total: ' . $students->count() . ' siswa');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);

            // Column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);

            // Center No column
            $sheet->getStyle('A4:A' . ($row - 1))->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $safeName = preg_replace('/[\/\\\\:*?"<>|]/', '_', $activity->nama_kegiatan);
        $filename = 'Peserta_' . str_replace(' ', '_', $safeName) . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'peserta_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
