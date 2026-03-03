<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamActivityGroup extends Model
{
    protected $fillable = ['exam_activity_id', 'nama_kelompok'];

    public function activity()
    {
        return $this->belongsTo(ExamActivity::class, 'exam_activity_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'exam_activity_group_students');
    }
}
