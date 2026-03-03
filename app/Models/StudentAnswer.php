<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
        'exam_session_id', 'student_id', 'question_id',
        'jawaban', 'is_flagged', 'is_correct', 'skor'
    ];

    protected function casts(): array
    {
        return ['is_flagged' => 'boolean', 'is_correct' => 'boolean'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }
}
