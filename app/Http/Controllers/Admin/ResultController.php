<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $sessions = ExamSession::with('categories.exam')
                    ->withCount('results')
                    ->whereHas('results')
                    ->latest()
                    ->get();
        return view('admin.results.index', compact('sessions'));
    }

    public function detail($sessionId)
    {
        $session = ExamSession::with('categories.exam')->findOrFail($sessionId);
        $results = ExamResult::where('exam_session_id', $sessionId)
                    ->with('student')
                    ->orderByDesc('skor')
                    ->get();
        return view('admin.results.detail', compact('session', 'results'));
    }

    public function studentDetail($sessionId, $studentId)
    {
        $session = ExamSession::with('categories.exam')->findOrFail($sessionId);

        // Get the student's answered questions (since questions are randomized per student)
        $answers = StudentAnswer::where('exam_session_id', $sessionId)
                    ->where('student_id', $studentId)
                    ->with('question.options')
                    ->get()
                    ->keyBy('question_id');

        // Get the actual questions this student received
        $questionIds = $answers->keys()->toArray();
        $questions = \App\Models\Question::with('options')->whereIn('id', $questionIds)->get();

        $result = ExamResult::where('exam_session_id', $sessionId)
                    ->where('student_id', $studentId)
                    ->first();
        $student = \App\Models\Student::findOrFail($studentId);

        return view('admin.results.student-detail', compact('session', 'questions', 'answers', 'result', 'student'));
    }
}
