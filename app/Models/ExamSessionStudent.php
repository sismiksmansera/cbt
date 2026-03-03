<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionStudent extends Model
{
    protected $fillable = ['exam_session_id', 'student_id', 'status', 'login_count', 'is_locked', 'waktu_mulai', 'waktu_selesai'];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
