<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('jawaban')->nullable()->comment('JSON for multiple/matching, text for others');
            $table->boolean('is_flagged')->default(false);
            $table->boolean('is_correct')->nullable();
            $table->decimal('skor', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['exam_session_id', 'student_id', 'question_id'], 'unique_answer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
