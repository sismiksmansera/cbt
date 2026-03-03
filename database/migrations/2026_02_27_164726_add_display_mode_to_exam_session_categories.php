<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_categories', function (Blueprint $table) {
            $table->string('display_mode')->default('semua')->after('exam_id'); // semua or sebagian
            $table->integer('jumlah_soal')->nullable()->after('display_mode');
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_categories', function (Blueprint $table) {
            $table->dropColumn(['display_mode', 'jumlah_soal']);
        });
    }
};
