<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (dipakai saat testing) tidak punya pg_constraint/regclass.
        // Cukup ubah kolomnya jadi string biasa — CHECK enum lama ikut hilang.
        if (DB::getDriverName() !== 'pgsql') {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('status', 20)->default('no_respon')->change();
            });
            return;
        }

        // Buang default & semua CHECK constraint pada kolom status (bawaan enum Postgres)
        DB::statement("ALTER TABLE leads ALTER COLUMN status DROP DEFAULT");
        DB::statement("ALTER TABLE leads ALTER COLUMN status TYPE VARCHAR(20) USING status::text");

        // Buang constraint enum lama jika masih ada (nama bisa beda, jaga-jaga)
        $constraints = DB::select("
            SELECT conname FROM pg_constraint
            WHERE conrelid = 'leads'::regclass AND contype = 'c' AND conname LIKE '%status%'
        ");
        foreach ($constraints as $c) {
            DB::statement('ALTER TABLE leads DROP CONSTRAINT IF EXISTS "' . $c->conname . '"');
        }

        // Petakan nilai lama → baru (untuk data yang sudah ada)
        DB::statement("UPDATE leads SET status = 'no_respon' WHERE status IN ('new', 'contacted')");
        DB::statement("UPDATE leads SET status = 'respon'    WHERE status = 'qualified'");
        DB::statement("UPDATE leads SET status = 'survey'    WHERE status = 'proposal'");
        DB::statement("UPDATE leads SET status = 'utj'       WHERE status = 'negotiation'");
        DB::statement("UPDATE leads SET status = 'closing'   WHERE status = 'won'");
        DB::statement("UPDATE leads SET status = 'batal'     WHERE status = 'lost'");
        DB::statement("UPDATE leads SET status = 'no_respon' WHERE status NOT IN ('no_respon','respon','kirim_pl','survey','utj','closing','batal')");

        // Pasang CHECK constraint baru + default
        DB::statement("ALTER TABLE leads ADD CONSTRAINT leads_status_check CHECK (status IN ('no_respon','respon','kirim_pl','survey','utj','closing','batal'))");
        DB::statement("ALTER TABLE leads ALTER COLUMN status SET DEFAULT 'no_respon'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE leads ALTER COLUMN status DROP DEFAULT");
        DB::statement("ALTER TABLE leads DROP CONSTRAINT IF EXISTS leads_status_check");
        DB::statement("UPDATE leads SET status = 'new'         WHERE status IN ('no_respon','respon')");
        DB::statement("UPDATE leads SET status = 'qualified'   WHERE status = 'kirim_pl'");
        DB::statement("UPDATE leads SET status = 'proposal'    WHERE status = 'survey'");
        DB::statement("UPDATE leads SET status = 'negotiation' WHERE status = 'utj'");
        DB::statement("UPDATE leads SET status = 'won'          WHERE status = 'closing'");
        DB::statement("UPDATE leads SET status = 'lost'         WHERE status = 'batal'");
        DB::statement("ALTER TABLE leads ALTER COLUMN status SET DEFAULT 'new'");
    }
};
