<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id', 'exam_activity_id', 'nama_sesi', 'durasi', 'token',
        'waktu_mulai', 'waktu_selesai', 'status'
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($session) {
            if (empty($session->token)) {
                $session->token = strtoupper(Str::random(6));
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'exam_session_students')
                    ->withPivot('status', 'waktu_mulai', 'waktu_selesai')
                    ->withTimestamps();
    }

    public function sessionStudents()
    {
        return $this->hasMany(ExamSessionStudent::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function categories()
    {
        return $this->hasMany(ExamSessionCategory::class)->orderBy('nomor_urut');
    }

    public function activity()
    {
        return $this->belongsTo(ExamActivity::class, 'exam_activity_id');
    }

    public function sessionGroups()
    {
        return $this->belongsToMany(ExamActivityGroup::class, 'exam_session_groups', 'exam_session_id', 'exam_activity_group_id');
    }

    public function questionGroups()
    {
        return $this->hasMany(ExamSessionQuestionGroup::class);
    }
}
