<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('from_number'); // nomor pengirim
            $table->string('to_number');   // nomor penerima
            $table->text('body');          // isi pesan
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->enum('channel', ['whatsapp', 'sms', 'email'])->default('whatsapp');
            $table->string('external_id')->nullable(); // ID dari Fonnte/WABA
            $table->json('metadata')->nullable(); // data tambahan dari API WA
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};