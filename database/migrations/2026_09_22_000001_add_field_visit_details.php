<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kunjungan lapangan: kolom yang dibutuhkan form aplikasi sales.
 *
 * leads  → profil calon pembeli yang ditanyakan saat survei
 * visits → detail kunjungan itu sendiri (snapshot, tidak berubah)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('occupation', 60)->nullable()->after('city');
            $table->string('salary_range', 20)->nullable()->after('occupation');
            $table->string('payment_method', 10)->nullable()->after('salary_range'); // kpr | cash
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('user_id')
                  ->constrained('leads')->nullOnDelete();
            $table->string('project_slug', 60)->nullable()->after('client_company');
            $table->string('unit_type_slug', 60)->nullable()->after('project_slug');
            $table->string('unit_block', 30)->nullable()->after('unit_type_slug');
            $table->string('interest_level', 10)->nullable()->after('unit_block');  // dingin|hangat|panas
            $table->string('came_with', 20)->nullable()->after('interest_level');   // sendiri|pasangan|keluarga|teman
            $table->string('next_action', 120)->nullable()->after('came_with');
            $table->date('next_action_date')->nullable()->after('next_action');
            $table->json('payload')->nullable()->after('next_action_date');        // snapshot form utuh
            $table->timestamp('server_captured_at')->nullable()->after('visited_at'); // waktu server, bukan HP
            $table->string('client_uuid', 40)->nullable()->unique()->after('id');   // idempoten untuk sinkron offline
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
            $table->dropColumn([
                'project_slug', 'unit_type_slug', 'unit_block', 'interest_level',
                'came_with', 'next_action', 'next_action_date', 'payload',
                'server_captured_at', 'client_uuid',
            ]);
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['occupation', 'salary_range', 'payment_method']);
        });
    }
};
