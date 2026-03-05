<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\ExamSessionStudent;
use App\Models\ExamSessionCategory;
use App\Models\ExamSessionQuestionGroup;
use App\Models\ExamSessionQgRombel;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\ExamActivityGroup;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Build the list of questions for this student's session.
     * Determines the student's question group based on their rombel/kelompok tes,
     * then loads categories from that group.
     * For each category:
     *   - 'semua': include all questions from that exam (shuffled order)
     *   - 'sebagian': pick N random questions from that exam
     * Cache the selection in session so the student gets consistent questions.
     */
    private function getSessionQuestions($sessionId, $studentId)
    {
        $cacheKey = "exam_questions_{$sessionId}_{$studentId}";

        if (session()->has($cacheKey)) {
            $questionIds = session($cacheKey);
            return Question::with('options')->whereIn('id', $questionIds)
                    ->get()
                    ->sortBy(function($q) use ($questionIds) {
                        return array_search($q->id, $questionIds);
                    })->values();
        }

        // Determine which question group applies to this student
        $student = Student::find($studentId);
        $session = ExamSession::with('questionGroups.rombels')->find($sessionId);

        $matchedGroupId = null;

        if ($session && $student) {
            // Get student's rombel name and check which groups they belong to
            $studentRombel = $student->kelas;

            // Also check if student belongs to a kelompok tes (ExamActivityGroup)
            $studentGroupNames = [];
            if ($session->exam_activity_id) {
                $activityGroups = ExamActivityGroup::where('exam_activity_id', $session->exam_activity_id)
                    ->whereHas('students', fn($q) => $q->where('students.id', $studentId))
                    ->pluck('nama_kelompok')
                    ->toArray();
                $studentGroupNames = $activityGroups;
            }

            // Find matching question group
            foreach ($session->questionGroups as $qg) {
                $qgRombels = $qg->rombels->pluck('rombel_name')->toArray();
                if (empty($qgRombels)) {
                    // No rombels assigned = default/fallback group
                    if (!$matchedGroupId) $matchedGroupId = $qg->id;
                    continue;
                }
                // Check if student's rombel or group name matches
                if (in_array($studentRombel, $qgRombels) || !empty(array_intersect($studentGroupNames, $qgRombels))) {
                    $matchedGroupId = $qg->id;
                    break;
                }
            }
        }

        // Build categories query
        $query = ExamSessionCategory::where('exam_session_id', $sessionId)->orderBy('nomor_urut');
        if ($matchedGroupId) {
            $query->where('question_group_id', $matchedGroupId);
        }
        $categories = $query->get();

        $questionIds = [];
        $usedIds = [];

        foreach ($categories as $cat) {
            // Check if the exam has an agama filter
            $exam = \App\Models\Exam::find($cat->exam_id);
            if ($exam && $exam->agama && $student) {
                // Skip this exam's questions if student's agama doesn't match
                if ($student->agama !== $exam->agama) {
                    continue;
                }
            }

            $q = Question::where('exam_id', $cat->exam_id);
            if (!empty($usedIds)) {
                $q->whereNotIn('id', $usedIds);
            }

            if ($cat->display_mode === 'sebagian' && $cat->jumlah_soal) {
                $ids = $q->inRandomOrder()
                    ->limit($cat->jumlah_soal)
                    ->pluck('id')
                    ->toArray();
            } else {
                $ids = $q->inRandomOrder()
                    ->pluck('id')
                    ->toArray();
            }

            $questionIds = array_merge($questionIds, $ids);
            $usedIds = array_merge($usedIds, $ids);
        }

        // Cache the selection
        session([$cacheKey => $questionIds]);

        return Question::with('options')->whereIn('id', $questionIds)
                ->get()
                ->sortBy(function($q) use ($questionIds) {
                    return array_search($q->id, $questionIds);
                })->values();
    }

    public function confirm()
    {
        $sessionId = session('exam_session_id');
        $studentId = session('student_id');

        $session = ExamSession::with(['categories.exam.subject', 'questionGroups.rombels'])->findOrFail($sessionId);
        $student = Student::findOrFail($studentId);

        $pivot = ExamSessionStudent::where('exam_session_id', $sessionId)
                ->where('student_id', $studentId)
                ->firstOrFail();

        if ($pivot->status === 'selesai') {
            $result = ExamResult::where('exam_session_id', $sessionId)
                        ->where('student_id', $studentId)->first();
            $showResult = $session->categories->contains(fn($cat) => $cat->exam && $cat->exam->show_result);
            return view('student.result', compact('session', 'result', 'showResult'));
        }

        // Match question group based on student's rombel (same logic as getSessionQuestions)
        $matchedGroupId = null;
        $studentRombel = $student->kelas;
        $studentGroupNames = [];

        if ($session->exam_activity_id) {
            $studentGroupNames = ExamActivityGroup::where('exam_activity_id', $session->exam_activity_id)
                ->whereHas('students', fn($q) => $q->where('students.id', $studentId))
                ->pluck('nama_kelompok')
                ->toArray();
        }

        foreach ($session->questionGroups as $qg) {
            $qgRombels = $qg->rombels->pluck('rombel_name')->toArray();
            if (empty($qgRombels)) {
                if (!$matchedGroupId) $matchedGroupId = $qg->id;
                continue;
            }
            if (in_array($studentRombel, $qgRombels) || !empty(array_intersect($studentGroupNames, $qgRombels))) {
                $matchedGroupId = $qg->id;
                break;
            }
        }

        // Get categories for matched group only
        $query = ExamSessionCategory::where('exam_session_id', $sessionId)->orderBy('nomor_urut');
        if ($matchedGroupId) {
            $query->where('question_group_id', $matchedGroupId);
        }
        $sessionCategories = $query->with('exam.subject')->get();

        // Filter by agama and map to display data
        $categories = $sessionCategories->filter(function($cat) use ($student) {
            if ($cat->exam && $cat->exam->agama && $student->agama) {
                return $student->agama === $cat->exam->agama;
            }
            return true;
        })->map(function($cat) {
            $subjectName = $cat->exam->subject->nama ?? 'Tidak diketahui';
            return [
                'nama' => $subjectName,
                'jumlah_soal' => $cat->display_mode === 'sebagian' ? $cat->jumlah_soal : ($cat->exam ? $cat->exam->questions()->count() : 0),
                'mode' => $cat->display_mode,
            ];
        });

        $durasi = $session->durasi ?? 60;

        return view('student.confirm', compact('session', 'student', 'categories', 'durasi', 'pivot'));
    }

    public function startExam()
    {
        session(['exam_confirmed' => true]);
        return redirect()->route('student.exam');
    }

    public function index()
    {
        $sessionId = session('exam_session_id');
        $studentId = session('student_id');

        // Redirect to confirm page if not confirmed yet
        if (!session('exam_confirmed')) {
            return redirect()->route('student.exam.confirm');
        }

        $session = ExamSession::with('categories.exam')->findOrFail($sessionId);

        $pivot = ExamSessionStudent::where('exam_session_id', $sessionId)
                ->where('student_id', $studentId)
                ->firstOrFail();

        if ($pivot->status === 'selesai') {
            $result = ExamResult::where('exam_session_id', $sessionId)
                        ->where('student_id', $studentId)->first();
            $showResult = $session->categories->contains(fn($cat) => $cat->exam && $cat->exam->show_result);
            return view('student.result', compact('session', 'result', 'showResult'));
        }

        $questions = $this->getSessionQuestions($sessionId, $studentId);

        // Shuffle options for each question
        foreach ($questions as $q) {
            if (in_array($q->tipe, ['multiple_choice', 'multiple_answer'])) {
                $q->setRelation('options', $q->options->shuffle());
            }
        }

        $answers = StudentAnswer::where('exam_session_id', $sessionId)
                    ->where('student_id', $studentId)
                    ->get()
                    ->keyBy('question_id');

        // Calculate remaining time
        $startTime = $pivot->waktu_mulai;
        $duration = ($session->durasi ?? 60) * 60; // seconds
        $elapsed = now()->diffInSeconds($startTime);
        $remaining = max(0, $duration - $elapsed);

        return view('student.exam', compact('session', 'questions', 'answers', 'remaining'));
    }

    public function saveAnswer(Request $request)
    {
        $sessionId = session('exam_session_id');
        $studentId = session('student_id');

        StudentAnswer::updateOrCreate(
            [
                'exam_session_id' => $sessionId,
                'student_id' => $studentId,
                'question_id' => $request->question_id,
            ],
            [
                'jawaban' => $request->jawaban,
                'is_flagged' => $request->is_flagged ?? false,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function toggleFlag(Request $request)
    {
        $sessionId = session('exam_session_id');
        $studentId = session('student_id');

        $answer = StudentAnswer::updateOrCreate(
            [
                'exam_session_id' => $sessionId,
                'student_id' => $studentId,
                'question_id' => $request->question_id,
            ],
            []
        );
        $answer->update(['is_flagged' => !$answer->is_flagged]);

        return response()->json(['flagged' => $answer->is_flagged]);
    }

    public function submit()
    {
        $sessionId = session('exam_session_id');
        $studentId = session('student_id');

        $session = ExamSession::findOrFail($sessionId);
        $questions = $this->getSessionQuestions($sessionId, $studentId);

        $answers = StudentAnswer::where('exam_session_id', $sessionId)
                    ->where('student_id', $studentId)
                    ->get()
                    ->keyBy('question_id');

        $totalSoal = $questions->count();
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
                    // Use percentage-based scoring if options have skor values
                    $totalPct = 0;
                    foreach ($q->options as $opt) {
                        if ($answeredIds->contains($opt->id)) {
                            $totalPct += $opt->skor; // e.g. +50 or -33.3333
                        }
                    }
                    $totalPct = max(0, min(100, $totalPct)); // Clamp 0-100
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

                case 'essay':
                    // Essay is not auto-graded
                    break;
            }

            $answer->update(['is_correct' => $isCorrect, 'skor' => $skor]);
            $totalSkor += $skor;
            if ($isCorrect) $benar++;
        }

        $finalScore = $maxSkor > 0 ? round(($totalSkor / $maxSkor) * 100, 2) : 0;
        $passingGrade = 70; // Default

        ExamResult::updateOrCreate(
            ['exam_session_id' => $sessionId, 'student_id' => $studentId],
            [
                'total_soal' => $totalSoal,
                'dijawab' => $dijawab,
                'benar' => $benar,
                'skor' => $finalScore,
                'lulus' => $finalScore >= $passingGrade,
                'waktu_selesai' => now(),
            ]
        );

        // Mark student as finished
        ExamSessionStudent::where('exam_session_id', $sessionId)
            ->where('student_id', $studentId)
            ->update(['status' => 'selesai', 'waktu_selesai' => now()]);

        // Clear cached questions
        session()->forget("exam_questions_{$sessionId}_{$studentId}");

        return response()->json(['success' => true, 'skor' => $finalScore]);
    }

    public function lockStudent(Request $request)
    {
        $sessionId = session('exam_session_id');
        $studentId = session('student_id');

        if (!$sessionId || !$studentId) {
            return response()->json(['success' => false]);
        }

        // Don't lock students who have already finished
        $pivot = ExamSessionStudent::where('exam_session_id', $sessionId)
            ->where('student_id', $studentId)->first();

        if ($pivot && $pivot->status === 'selesai') {
            session()->forget(['student_id', 'student_name', 'student_nisn', 'exam_session_id']);
            return response()->json(['success' => true, 'locked' => false]);
        }

        // Increment login count
        if ($pivot) {
            $pivot->increment('login_count');
            $pivot->refresh();

            // Check max login attempts
            $session = ExamSession::find($sessionId);
            $maxAttempts = $session->max_login_attempts ?? 1;

            if ($pivot->login_count >= $maxAttempts) {
                // Lock the student
                $pivot->update(['is_locked' => true]);
                session()->forget(['student_id', 'student_name', 'student_nisn', 'exam_session_id']);
                return response()->json(['success' => true, 'locked' => true]);
            }
        }

        // Under limit: logout but don't lock
        session()->forget(['student_id', 'student_name', 'student_nisn', 'exam_session_id', 'exam_confirmed']);

        return response()->json(['success' => true, 'locked' => false]);
    }
}
