<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamResult;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $totalExams = Exam::count();
        $activeSessions = ExamSession::where('status', 'active')->count();
        $avgScore = ExamResult::avg('skor') ?? 0;
        $recentResults = ExamResult::with(['student', 'session.exam'])
                        ->latest()
                        ->take(10)
                        ->get();
        $recentExams = Exam::with('subject')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalStudents', 'totalExams', 'activeSessions',
            'avgScore', 'recentResults', 'recentExams'
        ));
    }
}
