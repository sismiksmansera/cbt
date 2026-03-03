<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with('subject')->withCount('questions', 'sessions')->latest()->get();
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('nama')->get();
        return view('admin.exams.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'durasi' => 'required|integer|min:1',
        ]);

        Exam::create(array_merge($request->only(
            'kategori', 'subject_id', 'deskripsi', 'durasi',
            'passing_grade', 'shuffle_questions', 'shuffle_options', 'show_result'
        ), ['agama' => $request->input('agama') ?: null]));

        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit($id)
    {
        $exam = Exam::findOrFail($id);
        $subjects = Subject::orderBy('nama')->get();
        return view('admin.exams.edit', compact('exam', 'subjects'));
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $request->validate([
            'kategori' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'durasi' => 'required|integer|min:1',
        ]);

        $data = $request->only('kategori', 'subject_id', 'deskripsi', 'durasi', 'passing_grade', 'show_result');
        $data['agama'] = $request->input('agama') ?: null;
        $data['shuffle_questions'] = $request->has('shuffle_questions');
        $data['shuffle_options'] = $request->has('shuffle_options');
        $data['is_active'] = $request->has('is_active');
        $exam->update($data);

        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $exam = Exam::with('questions.options')->findOrFail($id);

        // Delete associated images from all questions
        foreach ($exam->questions as $question) {
            $htmlTexts = [$question->pertanyaan];
            foreach ($question->options as $opt) {
                $htmlTexts[] = $opt->teks_opsi;
            }
            foreach ($htmlTexts as $html) {
                if (preg_match_all('/src="(\/images\/questions\/[^"]+)"/', $html ?? '', $matches)) {
                    foreach ($matches[1] as $path) {
                        $fullPath = public_path($path);
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                }
            }
        }

        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dihapus.');
    }

    public function questions($id)
    {
        $exam = Exam::with(['questions.options', 'subject'])->findOrFail($id);
        return view('admin.exams.questions', compact('exam'));
    }
}
