<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['nama', 'nip', 'jenis_kelamin', 'jabatan', 'mapel_diampu', 'status', 'sumber'];
}
