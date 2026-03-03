<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_session_id', 'student_id', 'total_soal',
        'dijawab', 'benar', 'skor', 'lulus', 'waktu_selesai'
    ];

    protected function casts(): array
    {
        return ['lulus' => 'boolean', 'waktu_selesai' => 'datetime'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }
}
