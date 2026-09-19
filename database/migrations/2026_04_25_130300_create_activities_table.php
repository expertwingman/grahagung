<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'call',
                'meeting',
                'email',
                'whatsapp',
                'follow_up',
                'note',
                'status_change'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->morphs('subject'); // bisa lead, project, pipeline
            $table->enum('status', ['planned', 'done', 'cancelled'])->default('planned');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};