<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'exam_id', 'subject_id', 'tipe', 'pertanyaan',
        'gambar', 'bobot', 'urutan', 'pembahasan'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('urutan');
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
