<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = ['name', 'username', 'email', 'password'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
