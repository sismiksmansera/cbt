<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTeacher
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('teacher_id')) {
            return redirect()->route('teacher.login');
        }
        return $next($request);
    }
}
