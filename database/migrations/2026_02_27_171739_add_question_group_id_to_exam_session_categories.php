<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('question_group_id')->nullable()->after('id');
            $table->foreign('question_group_id')->references('id')->on('exam_session_question_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_categories', function (Blueprint $table) {
            $table->dropForeign(['question_group_id']);
            $table->dropColumn('question_group_id');
        });
    }
};
