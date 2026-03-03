<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'teks_opsi', 'teks_pasangan', 'is_correct', 'skor', 'urutan'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
