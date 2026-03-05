<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\ExamSessionStudent;
use App\Models\ExamSessionCategory;
use App\Models\ExamActivity;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ExamActivityGroup;
use App\Models\ExamSessionQuestionGroup;
use App\Models\ExamSessionQgRombel;
use Illuminate\Http\Request;

class ExamSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamSession::with(['categories.exam'])->withCount(['students', 'sessionGroups'])->latest();
        $activity = null;
        if ($request->activity_id) {
            $activity = ExamActivity::findOrFail($request->activity_id);
            $query->where('exam_activity_id', $activity->id);
        }
        $sessions = $query->get();
        return view('admin.exam-sessions.index', compact('sessions', 'activity'));
    }

    public function create(Request $request)
    {
        $exams = Exam::with('subject')->withCount('questions')->where('is_active', true)->orderBy('kategori')->get();
        $students = Student::where('is_active', true)->orderBy('nama')->get();
        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
    $rombelCounts = Student::where('is_active', true)->selectRaw('kelas, count(*) as total')->groupBy('kelas')->orderBy('kelas')->pluck('total', 'kelas');
        $teachers = Teacher::orderBy('nama')->get();
        $activity = null;
        if ($request->activity_id) {
            $activity = ExamActivity::with('groups.students')->findOrFail($request->activity_id);
        }
        return view('admin.exam-sessions.create', compact('exams', 'students', 'kelasList', 'rombelCounts', 'teachers', 'activity'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sesi' => 'required',
            'qg' => 'required|array|min:1',
            'durasi' => 'required|integer|min:1',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
        ]);

        $session = ExamSession::create([
            'nama_sesi' => $request->nama_sesi,
            'exam_activity_id' => $request->exam_activity_id,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'durasi' => $request->durasi,
        ]);

        // Save question groups with their categories and rombels
        foreach ($request->qg as $gIndex => $groupData) {
            $qg = ExamSessionQuestionGroup::create([
                'exam_session_id' => $session->id,
                'nama_kelompok_soal' => $groupData['name'] ?? 'Kelompok ' . $gIndex,
            ]);

            // Save rombel assignments
            if (!empty($groupData['rombels'])) {
                foreach ($groupData['rombels'] as $rombelName) {
                    ExamSessionQgRombel::create([
                        'question_group_id' => $qg->id,
                        'rombel_name' => $rombelName,
                    ]);
                }
            }

            // Save categories for this question group
            if (!empty($groupData['categories'])) {
                foreach ($groupData['categories'] as $catIndex => $examId) {
                    if ($examId) {
                        $mode = $groupData['display_mode'][$catIndex] ?? 'semua';
                        $jumlah = ($mode === 'sebagian') ? ($groupData['jumlah_soal'][$catIndex] ?? null) : null;
                        ExamSessionCategory::create([
                            'exam_session_id' => $session->id,
                            'question_group_id' => $qg->id,
                            'nomor_urut' => $catIndex,
                            'exam_id' => $examId,
                            'display_mode' => $mode,
                            'jumlah_soal' => $jumlah,
                        ]);
                    }
                }
            }
        }

        // Add students
        $studentIds = $request->student_ids ?? [];

        // If groups were selected, resolve to student IDs and save group links
        if ($request->group_ids && is_array($request->group_ids)) {
            $studentIds = ExamActivityGroup::whereIn('id', $request->group_ids)
                ->with('students')
                ->get()
                ->flatMap(fn($g) => $g->students->pluck('id'))
                ->unique()
                ->toArray();

            // Save which groups were selected for this session
            $session->sessionGroups()->sync($request->group_ids);
        } elseif ($request->select_by_kelas && $request->kelas_selected) {
            $studentIds = Student::where('is_active', true)
                        ->whereIn('kelas', $request->kelas_selected)
                        ->pluck('id')
                        ->toArray();
        }

        foreach ($studentIds as $sid) {
            ExamSessionStudent::create([
                'exam_session_id' => $session->id,
                'student_id' => $sid,
            ]);
        }

        $redirectParams = $session->exam_activity_id ? ['activity_id' => $session->exam_activity_id] : [];
        return redirect()->route('admin.exam-sessions.index', $redirectParams)
                        ->with('success', "Setting ujian berhasil dibuat. Token: {$session->token}");
    }

    public function monitor($id)
    {
        $session = ExamSession::with(['categories.exam', 'students', 'sessionGroups.students', 'questionGroups.rombels'])->findOrFail($id);
        $sessionStudents = ExamSessionStudent::where('exam_session_id', $id)
                          ->with('student')
                          ->get();

        // Build student_id -> group_name mapping
        $studentGroupMap = [];
        $groups = $session->sessionGroups;
        foreach ($groups as $group) {
            foreach ($group->students as $student) {
                $studentGroupMap[$student->id] = $group->nama_kelompok;
            }
        }

        // Build student_id -> mapel mapping via question groups & rombels
        // Build rombel_name -> question group name map first
        $rombelToMapel = [];
        foreach ($session->questionGroups as $qg) {
            foreach ($qg->rombels as $rombel) {
                $rombelToMapel[$rombel->rombel_name] = $qg->nama_kelompok_soal;
            }
        }

        $studentMapelMap = [];
        foreach ($sessionStudents as $ss) {
            // Try matching by group name (works for rombel-mode groups)
            $groupName = $studentGroupMap[$ss->student_id] ?? '';
            if ($groupName && isset($rombelToMapel[$groupName])) {
                $studentMapelMap[$ss->student_id] = $rombelToMapel[$groupName];
            }
            // Fallback: match by student kelas (works for custom groups)
            elseif ($ss->student && isset($rombelToMapel[$ss->student->kelas])) {
                $studentMapelMap[$ss->student_id] = $rombelToMapel[$ss->student->kelas];
            }
        }

        return view('admin.exam-sessions.monitor', compact('session', 'sessionStudents', 'groups', 'studentGroupMap', 'studentMapelMap'));
    }

    public function toggleStatus($id)
    {
        $session = ExamSession::findOrFail($id);
        if ($session->status === 'pending') {
            $session->update(['status' => 'active']);
            $msg = 'Sesi ujian diaktifkan.';
        } elseif ($session->status === 'active') {
            $session->update(['status' => 'finished']);
            ExamSessionStudent::where('exam_session_id', $id)
                ->where('status', 'mengerjakan')
                ->update(['status' => 'selesai', 'waktu_selesai' => now()]);
            $msg = 'Sesi ujian diakhiri.';
        } else {
            $msg = 'Sesi sudah selesai.';
        }
        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        ExamSession::findOrFail($id)->delete();
        return redirect()->route('admin.exam-sessions.index')->with('success', 'Setting ujian berhasil dihapus.');
    }

    public function edit($id)
    {
        $session = ExamSession::with(['categories', 'students', 'questionGroups.rombels', 'questionGroups.categories'])->findOrFail($id);
        $exams = Exam::with('subject')->withCount('questions')->where('is_active', true)->orderBy('kategori')->get();
        $students = Student::where('is_active', true)->orderBy('nama')->get();
        $kelasList = Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $rombelCounts = Student::where('is_active', true)->selectRaw('kelas, count(*) as total')->groupBy('kelas')->orderBy('kelas')->pluck('total', 'kelas');

        $selectedStudentIds = $session->students->pluck('student_id')->toArray();
        $existingQuestionGroups = $session->questionGroups;

        // Load activity with groups for test group selection
        $activity = null;
        $selectedGroupIds = [];
        if ($session->exam_activity_id) {
            $activity = \App\Models\ExamActivity::with('groups.students')->find($session->exam_activity_id);
            $selectedGroupIds = $session->sessionGroups()->pluck('exam_activity_group_id')->toArray();
        }

        return view('admin.exam-sessions.edit', compact('session', 'exams', 'students', 'kelasList', 'rombelCounts', 'selectedStudentIds', 'existingQuestionGroups', 'activity', 'selectedGroupIds'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_sesi' => 'required',
            'durasi' => 'required|integer|min:1',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
        ]);

        $session = ExamSession::findOrFail($id);
        $session->update([
            'nama_sesi' => $request->nama_sesi,
            'durasi' => $request->durasi,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
        ]);

        // Delete old question groups (cascades to categories & rombels)
        ExamSessionQuestionGroup::where('exam_session_id', $id)->delete();
        // Also delete any orphan categories without question_group_id
        ExamSessionCategory::where('exam_session_id', $id)->delete();

        // Save question groups with their categories and rombels
        if ($request->qg) {
            foreach ($request->qg as $gIndex => $groupData) {
                $qg = ExamSessionQuestionGroup::create([
                    'exam_session_id' => $session->id,
                    'nama_kelompok_soal' => $groupData['name'] ?? 'Kelompok ' . $gIndex,
                ]);

                if (!empty($groupData['rombels'])) {
                    foreach ($groupData['rombels'] as $rombelName) {
                        ExamSessionQgRombel::create([
                            'question_group_id' => $qg->id,
                            'rombel_name' => $rombelName,
                        ]);
                    }
                }

                if (!empty($groupData['categories'])) {
                    foreach ($groupData['categories'] as $catIndex => $examId) {
                        if ($examId) {
                            $mode = $groupData['display_mode'][$catIndex] ?? 'semua';
                            $jumlah = ($mode === 'sebagian') ? ($groupData['jumlah_soal'][$catIndex] ?? null) : null;
                            ExamSessionCategory::create([
                                'exam_session_id' => $session->id,
                                'question_group_id' => $qg->id,
                                'nomor_urut' => $catIndex,
                                'exam_id' => $examId,
                                'display_mode' => $mode,
                                'jumlah_soal' => $jumlah,
                            ]);
                        }
                    }
                }
            }
        }

        // Sync test groups and students
        if ($request->has('group_ids') && is_array($request->group_ids)) {
            // Sync session-group pivot
            $session->sessionGroups()->sync($request->group_ids);

            // Collect students from selected groups
            $studentIds = ExamActivityGroup::whereIn('id', $request->group_ids)
                ->with('students')
                ->get()
                ->flatMap(fn($g) => $g->students->pluck('id'))
                ->unique()
                ->toArray();

            // Remove students not in selected groups
            ExamSessionStudent::where('exam_session_id', $id)
                ->whereNotIn('student_id', $studentIds)
                ->delete();

            // Add new students
            foreach ($studentIds as $sid) {
                ExamSessionStudent::firstOrCreate([
                    'exam_session_id' => $id,
                    'student_id' => $sid,
                ]);
            }
        }

        $redirectParams = $session->exam_activity_id ? ['activity_id' => $session->exam_activity_id] : [];
        return redirect()->route('admin.exam-sessions.index', $redirectParams)
            ->with('success', 'Setting ujian berhasil diperbarui.');
    }

    public function restart($id)
    {
        $session = ExamSession::findOrFail($id);

        // Reset all student statuses
        ExamSessionStudent::where('exam_session_id', $id)->update([
            'status' => 'belum_mulai',
            'waktu_mulai' => null,
            'waktu_selesai' => null,
            'login_count' => 0,
            'is_locked' => false,
        ]);

        // Delete results and answers
        \App\Models\ExamResult::where('exam_session_id', $id)->delete();
        \App\Models\StudentAnswer::where('exam_session_id', $id)->delete();
        \App\Models\AttendanceConfirmation::where('exam_session_id', $id)->delete();

        // Set status back to active
        $session->update(['status' => 'active']);

        return redirect()->route('admin.exam-sessions.index')
            ->with('success', 'Sesi ujian "' . $session->nama_sesi . '" berhasil dimulai kembali. Semua data siswa telah direset.');
    }

    public function resetStudents(Request $request, $id)
    {
        $session = ExamSession::findOrFail($id);
        $studentIds = $request->input('student_ids', []);
        $resetAll = $request->input('reset_all', false);

        $query = ExamSessionStudent::where('exam_session_id', $id);
        if (!$resetAll && !empty($studentIds)) {
            $query->whereIn('student_id', $studentIds);
        }

        $affectedIds = $query->pluck('student_id')->toArray();

        if (empty($affectedIds)) {
            return back()->with('success', 'Tidak ada siswa yang dipilih untuk direset.');
        }

        // Reset student statuses
        ExamSessionStudent::where('exam_session_id', $id)
            ->whereIn('student_id', $affectedIds)
            ->update([
                'status' => 'belum_mulai',
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'login_count' => 0,
                'is_locked' => false,
            ]);

        // Delete results
        \App\Models\ExamResult::where('exam_session_id', $id)
            ->whereIn('student_id', $affectedIds)->delete();

        // Delete answers
        \App\Models\StudentAnswer::where('exam_session_id', $id)
            ->whereIn('student_id', $affectedIds)->delete();

        // Delete attendance confirmations
        \App\Models\AttendanceConfirmation::where('exam_session_id', $id)
            ->whereIn('student_id', $affectedIds)->delete();

        $count = count($affectedIds);
        $msg = $resetAll ? "Seluruh $count siswa berhasil direset." : "$count siswa terpilih berhasil direset.";
        return back()->with('success', $msg);
    }

    public function unlockStudent($id, $studentId)
    {
        ExamSessionStudent::where('exam_session_id', $id)
            ->where('student_id', $studentId)
            ->update(['is_locked' => false]);

        return back()->with('success', 'Siswa berhasil dibuka kuncinya.');
    }

    public function unlockAll($id)
    {
        $count = ExamSessionStudent::where('exam_session_id', $id)
            ->where('is_locked', true)
            ->update(['is_locked' => false]);

        return back()->with('success', "$count siswa berhasil dibuka kuncinya.");
    }

    public function updateMaxAttempts(Request $request, $id)
    {
        $session = ExamSession::findOrFail($id);
        $session->update(['max_login_attempts' => max(1, (int) $request->input('max_login_attempts', 1))]);
        return back()->with('success', 'Maks upaya login diperbarui menjadi ' . $session->max_login_attempts . 'x.');
    }

    public function exportResults($id)
    {
        $session = ExamSession::with(['sessionGroups.students', 'questionGroups.rombels'])->findOrFail($id);
        $sessionStudents = ExamSessionStudent::where('exam_session_id', $id)->with('student')->get();

        // Build maps
        $studentGroupMap = [];
        foreach ($session->sessionGroups as $group) {
            foreach ($group->students as $student) {
                $studentGroupMap[$student->id] = $group->nama_kelompok;
            }
        }

        // Get results
        $results = \App\Models\ExamResult::where('exam_session_id', $id)->get()->keyBy('student_id');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Ujian');

        // Headers
        $headers = ['No', 'Nama', 'NISN', 'Kelas', 'Kelompok Tes', 'Status', 'Waktu Mulai', 'Waktu Selesai', 'Skor (%)'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Data
        $row = 2;
        $no = 1;
        foreach ($sessionStudents as $ss) {
            $student = $ss->student;
            if (!$student) continue;
            $result = $results->get($ss->student_id);
            $status = $ss->status === 'mengerjakan' ? 'Mengerjakan' : ($ss->status === 'selesai' ? 'Selesai' : 'Belum Mulai');

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $student->nama ?? '-');
            $sheet->setCellValueExplicit('C' . $row, $student->nisn ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $row, $student->kelas ?? '-');
            $sheet->setCellValue('E' . $row, $studentGroupMap[$ss->student_id] ?? '-');
            $sheet->setCellValue('F' . $row, $status);
            $sheet->setCellValue('G' . $row, $ss->waktu_mulai ? \Carbon\Carbon::parse($ss->waktu_mulai)->format('H:i:s') : '-');
            $sheet->setCellValue('H' . $row, $ss->waktu_selesai ? \Carbon\Carbon::parse($ss->waktu_selesai)->format('H:i:s') : '-');
            $sheet->setCellValue('I' . $row, $result ? $result->skor . '%' : '-');

            $row++;
            $no++;
        }

        // Auto width
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Hasil_Ujian_' . str_replace(' ', '_', $session->nama_sesi) . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function lockStudent($id, $studentId)
    {
        ExamSessionStudent::where('exam_session_id', $id)
            ->where('student_id', $studentId)
            ->update(['is_locked' => true]);

        return back()->with('success', 'Siswa berhasil dikunci.');
    }

    public function forceSubmit($id, $studentId)
    {
        $session = ExamSession::findOrFail($id);

        // Get the questions this student was assigned
        $answers = \App\Models\StudentAnswer::where('exam_session_id', $id)
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('question_id');

        // Get categories for this session
        $categoryIds = ExamSessionCategory::where('exam_session_id', $id)
            ->orderBy('nomor_urut')
            ->pluck('exam_id');

        // Load all questions from these categories
        $allQuestionIds = $answers->pluck('question_id')->toArray();
        $questions = \App\Models\Question::with('options')
            ->whereIn('id', $allQuestionIds)
            ->get();

        $totalSoal = $questions->count() > 0 ? $questions->count() : $categoryIds->count();
        $dijawab = 0;
        $benar = 0;
        $totalSkor = 0;
        $maxSkor = 0;

        foreach ($questions as $q) {
            $maxSkor += $q->bobot;
            $answer = $answers->get($q->id);
            if (!$answer || empty($answer->jawaban)) continue;

            $dijawab++;
            $isCorrect = false;
            $skor = 0;

            switch ($q->tipe) {
                case 'multiple_choice':
                case 'true_false':
                    $correctOption = $q->options->where('is_correct', true)->first();
                    if ($correctOption && $answer->jawaban == $correctOption->id) {
                        $isCorrect = true;
                        $skor = $q->bobot;
                    }
                    break;
                case 'multiple_answer':
                    $answeredIds = collect(json_decode($answer->jawaban, true) ?? []);
                    $totalPct = 0;
                    foreach ($q->options as $opt) {
                        if ($answeredIds->contains($opt->id)) {
                            $totalPct += $opt->skor;
                        }
                    }
                    $totalPct = max(0, min(100, $totalPct));
                    $skor = round(($totalPct / 100) * $q->bobot, 4);
                    $isCorrect = $totalPct >= 100;
                    break;
                case 'matching':
                    $pairs = json_decode($answer->jawaban, true) ?? [];
                    $allCorrect = true;
                    foreach ($q->options as $opt) {
                        if (!isset($pairs[$opt->id]) || $pairs[$opt->id] != $opt->teks_pasangan) {
                            $allCorrect = false;
                            break;
                        }
                    }
                    if ($allCorrect && count($pairs) === $q->options->count()) {
                        $isCorrect = true;
                        $skor = $q->bobot;
                    }
                    break;
                case 'short_answer':
                    $correctAnswer = $q->options->where('is_correct', true)->first();
                    if ($correctAnswer && strtolower(trim($answer->jawaban)) === strtolower(trim($correctAnswer->teks_opsi))) {
                        $isCorrect = true;
                        $skor = $q->bobot;
                    }
                    break;
            }

            $answer->update(['is_correct' => $isCorrect, 'skor' => $skor]);
            $totalSkor += $skor;
            if ($isCorrect) $benar++;
        }

        $finalScore = $maxSkor > 0 ? round(($totalSkor / $maxSkor) * 100, 2) : 0;

        \App\Models\ExamResult::updateOrCreate(
            ['exam_session_id' => $id, 'student_id' => $studentId],
            [
                'total_soal' => $totalSoal,
                'dijawab' => $dijawab,
                'benar' => $benar,
                'skor' => $finalScore,
                'lulus' => $finalScore >= 70,
                'waktu_selesai' => now(),
            ]
        );

        ExamSessionStudent::where('exam_session_id', $id)
            ->where('student_id', $studentId)
            ->update(['status' => 'selesai', 'is_locked' => false, 'waktu_selesai' => now()]);

        return back()->with('success', 'Ujian siswa berhasil dikumpulkan paksa. Skor: ' . $finalScore);
    }

    public function addStudent(Request $request, $id)
    {
        $request->validate(['student_id' => 'required|exists:students,id']);

        $exists = ExamSessionStudent::where('exam_session_id', $id)
            ->where('student_id', $request->student_id)->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Siswa sudah terdaftar di sesi ini.']);
        }

        ExamSessionStudent::create([
            'exam_session_id' => $id,
            'student_id' => $request->student_id,
        ]);

        $student = Student::find($request->student_id);
        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan.',
            'student' => [
                'id' => $student->id,
                'nama' => $student->nama,
                'nisn' => $student->nisn,
                'kelas' => $student->kelas,
            ]
        ]);
    }

    public function removeStudent($id, $studentId)
    {
        ExamSessionStudent::where('exam_session_id', $id)
            ->where('student_id', $studentId)->delete();

        return response()->json(['success' => true, 'message' => 'Siswa berhasil dihapus dari sesi.']);
    }

    public function syncGroups(Request $request, $id)
    {
        $session = ExamSession::findOrFail($id);
        $groupIds = $request->input('group_ids', []);

        // Sync session-group pivot
        $session->sessionGroups()->sync($groupIds);

        // Collect students from selected groups
        $studentIds = ExamActivityGroup::whereIn('id', $groupIds)
            ->with('students')
            ->get()
            ->flatMap(fn($g) => $g->students->pluck('id'))
            ->unique()
            ->toArray();

        // Remove students not in selected groups
        ExamSessionStudent::where('exam_session_id', $id)
            ->whereNotIn('student_id', $studentIds)
            ->delete();

        // Add new students
        foreach ($studentIds as $sid) {
            ExamSessionStudent::firstOrCreate([
                'exam_session_id' => $id,
                'student_id' => $sid,
            ]);
        }

        // Return refreshed student list
        $students = Student::whereIn('id', $studentIds)->orderBy('nama')->get()
            ->map(function($s) {
                return ['id' => $s->id, 'nama' => $s->nama, 'nisn' => $s->nisn, 'kelas' => $s->kelas];
            });

        return response()->json([
            'success' => true,
            'message' => count($studentIds) . ' siswa disinkronkan.',
            'students' => $students,
        ]);
    }

    public function printAttendance($id)
    {
        $session = ExamSession::with([
            'activity',
            'sessionGroups.students',
            'categories.exam.subject',
        ])->findOrFail($id);

        // Get mata pelajaran from session categories
        $subjects = $session->categories
            ->map(fn($c) => $c->exam->subject->nama ?? null)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        // Build groups with their students
        $groups = $session->sessionGroups->map(function($group) {
            return [
                'nama' => $group->nama_kelompok,
                'students' => $group->students->sortBy('nama')->values(),
            ];
        });

        return view('admin.exam-sessions.print-attendance', compact('session', 'subjects', 'groups'));
    }
}
