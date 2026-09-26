<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Papan konten — dari GAK-DMOS, diintegrasikan ke CRM.
 * content_items  = ide, draf, konten yang dijadwalkan atau dipublikasikan
 * content_publications = tautan per platform setelah dipublikasikan
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('content_id', 30)->unique();              // GSR-20260922-ABCD
            $table->string('title');
            $table->text('idea')->nullable();                         // brief / ide awal
            $table->longText('caption')->nullable();                  // caption siap publish
            $table->string('platform', 50)->nullable();               // instagram, tiktok, youtube, blog, whatsapp
            $table->string('content_type', 50)->nullable();           // reel, carousel, artikel, story, broadcast
            $table->string('status', 20)->default('idea');            // idea, planned, production, review, approved, published, archived
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('seo_score')->default(0);
            $table->unsignedTinyInteger('quality_score')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('content_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 50);
            $table->string('published_url')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('status', 20)->default('published');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_publications');
        Schema::dropIfExists('content_items');
    }
};
