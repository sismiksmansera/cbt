<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionQuestionGroup extends Model
{
    protected $fillable = [
        'exam_session_id', 'nama_kelompok_soal'
    ];

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function categories()
    {
        return $this->hasMany(ExamSessionCategory::class, 'question_group_id')->orderBy('nomor_urut');
    }

    public function rombels()
    {
        return $this->hasMany(ExamSessionQgRombel::class, 'question_group_id');
    }
}
