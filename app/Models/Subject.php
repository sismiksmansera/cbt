<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['nama', 'kode', 'deskripsi'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
