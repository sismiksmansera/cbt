<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'kategori', 'subject_id', 'agama', 'deskripsi', 'durasi',
        'passing_grade', 'shuffle_questions', 'shuffle_options',
        'show_result', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_result' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('urutan');
    }

    public function sessions()
    {
        return $this->hasMany(ExamSession::class);
    }
}
