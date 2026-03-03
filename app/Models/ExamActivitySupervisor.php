<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamActivitySupervisor extends Model
{
    protected $fillable = ['exam_activity_id', 'teacher_id', 'nama_pengawas', 'nip', 'jenis_kelamin', 'asal_instansi', 'is_external'];

    public function activity()
    {
        return $this->belongsTo(ExamActivity::class, 'exam_activity_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
