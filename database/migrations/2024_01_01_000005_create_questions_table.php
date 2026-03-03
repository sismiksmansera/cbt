<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->enum('tipe', ['multiple_choice', 'multiple_answer', 'true_false', 'matching', 'short_answer', 'essay']);
            $table->text('pertanyaan');
            $table->text('gambar')->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->integer('urutan')->default(0);
            $table->text('pembahasan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
