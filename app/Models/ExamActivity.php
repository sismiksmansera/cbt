<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamActivity extends Model
{
    protected $fillable = ['nama_kegiatan', 'tanggal_pelaksanaan', 'tanggal_selesai', 'peserta_ujian', 'kelompok_tes_mode'];

    public function supervisors()
    {
        return $this->hasMany(ExamActivitySupervisor::class);
    }

    public function groups()
    {
        return $this->hasMany(ExamActivityGroup::class);
    }

    public function sessions()
    {
        return $this->hasMany(ExamSession::class);
    }
}
