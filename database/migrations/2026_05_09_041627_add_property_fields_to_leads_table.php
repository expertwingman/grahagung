<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('budget_range')->nullable();       // Range Budget
            $table->string('interest_type')->nullable();      // Minat Tipe unit
            $table->string('location_interest')->nullable();  // Lokasi Minat
            $table->date('follow_up_date')->nullable();       // Tanggal FU Terakhir
            $table->text('survey_plan')->nullable();          // Rencana Survey
            $table->text('survey_result')->nullable();        // Hasil Survey
            $table->boolean('utj_status')->default(false);   // UTJ Ya/Tidak
            $table->date('utj_date')->nullable();             // Tanggal UTJ
            $table->text('cancel_reason')->nullable();        // Alasan Pending/Batal
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'budget_range', 'interest_type', 'location_interest',
                'follow_up_date', 'survey_plan', 'survey_result',
                'utj_status', 'utj_date', 'cancel_reason',
            ]);
        });
    }
};