<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_session_qg_rombels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_group_id');
            $table->string('rombel_name');
            $table->timestamps();

            $table->foreign('question_group_id')->references('id')->on('exam_session_question_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_session_qg_rombels');
    }
};
