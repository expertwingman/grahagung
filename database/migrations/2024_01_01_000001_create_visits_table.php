<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // sales
            $table->unsignedBigInteger('client_id')->nullable();              // klien existing di CRM
            $table->string('client_name');                                    // nama klien (baru atau existing)
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_company')->nullable();
            $table->text('notes')->nullable();                                // catatan sales
            $table->string('status')->default('visited');                     // visited, follow_up, deal, cancel
            $table->decimal('latitude', 10, 8)->nullable();                  // GPS lat
            $table->decimal('longitude', 11, 8)->nullable();                 // GPS lng
            $table->string('location_address')->nullable();                  // alamat hasil reverse geocode
            $table->timestamp('visited_at');                                  // waktu ketemu klien
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
