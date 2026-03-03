<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    protected $fillable = ['nisn', 'nama', 'kelas', 'jenis_kelamin', 'agama', 'password', 'is_active'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function examSessions()
    {
        return $this->belongsToMany(ExamSession::class, 'exam_session_students')
                    ->withPivot('status', 'waktu_mulai', 'waktu_selesai')
                    ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }
}
