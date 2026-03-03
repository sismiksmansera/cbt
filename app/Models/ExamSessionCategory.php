<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionCategory extends Model
{
    protected $fillable = [
        'exam_session_id', 'question_group_id', 'nomor_urut', 'exam_id', 'display_mode', 'jumlah_soal'
    ];

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function questionGroup()
    {
        return $this->belongsTo(ExamSessionQuestionGroup::class, 'question_group_id');
    }
}
