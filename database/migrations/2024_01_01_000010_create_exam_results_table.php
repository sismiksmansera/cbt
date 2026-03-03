<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('total_soal')->default(0);
            $table->integer('dijawab')->default(0);
            $table->integer('benar')->default(0);
            $table->decimal('skor', 5, 2)->default(0);
            $table->boolean('lulus')->default(false);
            $table->dateTime('waktu_selesai')->nullable();
            $table->timestamps();

            $table->unique(['exam_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
