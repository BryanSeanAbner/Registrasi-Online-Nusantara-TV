<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blast_id')->nullable()->constrained('wa_blasts')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('code')->nullable();
            $table->string('status', 20)->default('queued');
            $table->text('error')->nullable();
            $table->text('provider_response')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'registration_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
    }
};

