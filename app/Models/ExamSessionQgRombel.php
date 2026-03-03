<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionQgRombel extends Model
{
    protected $fillable = [
        'question_group_id', 'rombel_name'
    ];

    public function questionGroup()
    {
        return $this->belongsTo(ExamSessionQuestionGroup::class, 'question_group_id');
    }
}
