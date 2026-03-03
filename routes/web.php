<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamSessionController;
use App\Http\Controllers\Admin\ExamActivityController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Auth\TeacherLoginController;
use App\Http\Controllers\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Student\ExamController as StudentExamController;

// Home → Role Selector
Route::get('/', function () {
    return view('auth.role-selector');
})->name('home');

// Admin Auth
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Student Auth
Route::get('/login', [StudentLoginController::class, 'showLoginForm'])->name('student.login');
Route::post('/login', [StudentLoginController::class, 'login'])->name('student.login.submit');
Route::post('/student/logout', [StudentLoginController::class, 'logout'])->name('student.logout');

// Teacher Auth
Route::get('/teacher/login', [TeacherLoginController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherLoginController::class, 'login'])->name('teacher.login.submit');
Route::post('/teacher/logout', [TeacherLoginController::class, 'logout'])->name('teacher.logout');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware('check.admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::match(['get', 'post'], '/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::match(['get', 'post'], '/students/sync-simas', [StudentController::class, 'syncFromSimas'])->name('students.sync-simas');

    // Subjects
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects/sync-simas', [SubjectController::class, 'syncFromSimas'])->name('subjects.sync-simas');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Teachers
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers/sync-simas', [TeacherController::class, 'syncFromSimas'])->name('teachers.sync-simas');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

    // Exams
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{id}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{id}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{id}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::get('/exams/{id}/questions', [ExamController::class, 'questions'])->name('exams.questions');

    // Questions
    Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{id}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('/questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::post('/questions/import-answer-key', [QuestionController::class, 'importAnswerKey'])->name('questions.import-answer-key');

    // Exam Sessions
    Route::get('/exam-sessions', [ExamSessionController::class, 'index'])->name('exam-sessions.index');
    Route::get('/exam-sessions/create', [ExamSessionController::class, 'create'])->name('exam-sessions.create');
    Route::post('/exam-sessions', [ExamSessionController::class, 'store'])->name('exam-sessions.store');
    Route::get('/exam-sessions/{id}/monitor', [ExamSessionController::class, 'monitor'])->name('exam-sessions.monitor');
    Route::post('/exam-sessions/{id}/toggle-status', [ExamSessionController::class, 'toggleStatus'])->name('exam-sessions.toggle-status');
    Route::delete('/exam-sessions/{id}', [ExamSessionController::class, 'destroy'])->name('exam-sessions.destroy');
    Route::get('/exam-sessions/{id}/edit', [ExamSessionController::class, 'edit'])->name('exam-sessions.edit');
    Route::put('/exam-sessions/{id}', [ExamSessionController::class, 'update'])->name('exam-sessions.update');
    Route::post('/exam-sessions/{id}/restart', [ExamSessionController::class, 'restart'])->name('exam-sessions.restart');
    Route::post('/exam-sessions/{id}/unlock-student/{studentId}', [ExamSessionController::class, 'unlockStudent'])->name('exam-sessions.unlock-student');
    Route::post('/exam-sessions/{id}/force-submit/{studentId}', [ExamSessionController::class, 'forceSubmit'])->name('exam-sessions.force-submit');
    Route::post('/exam-sessions/{id}/add-student', [ExamSessionController::class, 'addStudent'])->name('exam-sessions.add-student');
    Route::delete('/exam-sessions/{id}/remove-student/{studentId}', [ExamSessionController::class, 'removeStudent'])->name('exam-sessions.remove-student');
    Route::post('/exam-sessions/{id}/sync-groups', [ExamSessionController::class, 'syncGroups'])->name('exam-sessions.sync-groups');

    // Exam Activities
    Route::get('/exam-activities', [ExamActivityController::class, 'index'])->name('exam-activities.index');
    Route::get('/exam-activities/create', [ExamActivityController::class, 'create'])->name('exam-activities.create');
    Route::post('/exam-activities', [ExamActivityController::class, 'store'])->name('exam-activities.store');
    Route::get('/exam-activities/{id}/edit', [ExamActivityController::class, 'edit'])->name('exam-activities.edit');
    Route::put('/exam-activities/{id}', [ExamActivityController::class, 'update'])->name('exam-activities.update');
    Route::delete('/exam-activities/{id}', [ExamActivityController::class, 'destroy'])->name('exam-activities.destroy');

    // Results
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{sessionId}', [ResultController::class, 'detail'])->name('results.detail');
    Route::get('/results/{sessionId}/student/{studentId}', [ResultController::class, 'studentDetail'])->name('results.student-detail');
});

// Student Exam Routes
Route::prefix('student')->name('student.')->middleware('check.student')->group(function () {
    Route::get('/exam', [StudentExamController::class, 'index'])->name('exam');
    Route::post('/exam/save-answer', [StudentExamController::class, 'saveAnswer'])->name('exam.save-answer');
    Route::post('/exam/toggle-flag', [StudentExamController::class, 'toggleFlag'])->name('exam.toggle-flag');
    Route::post('/exam/submit', [StudentExamController::class, 'submit'])->name('exam.submit');
    Route::post('/exam/lock', [StudentExamController::class, 'lockStudent'])->name('exam.lock');
});

// Teacher Routes
Route::prefix('teacher')->name('teacher.')->middleware('check.teacher')->group(function () {
    Route::get('/dashboard', [TeacherExamController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance/{sessionId}', [TeacherExamController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/{sessionId}', [TeacherExamController::class, 'saveAttendance'])->name('save-attendance');
    Route::get('/monitor/{sessionId}', [TeacherExamController::class, 'monitor'])->name('monitor');
});
