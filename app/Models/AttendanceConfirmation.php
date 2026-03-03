<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceConfirmation extends Model
{
    protected $fillable = ['exam_session_id', 'student_id', 'teacher_id', 'kelas', 'status', 'confirmed_at'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }
}
