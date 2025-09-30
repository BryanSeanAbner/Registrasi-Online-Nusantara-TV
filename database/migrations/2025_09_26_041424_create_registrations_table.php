<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // Scope ke event
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            // Identitas dasar/opsional (bisa dipakai untuk kode tiket, QR, status, dsb)
            $table->string('code')->nullable()->index(); // mis. kode registrasi/QR
            $table->string('status')->default('pending'); // pending|approved|rejected|checked_in
            $table->timestamp('checked_in_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
