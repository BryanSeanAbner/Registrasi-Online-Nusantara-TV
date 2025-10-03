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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('section')->nullable();
            $table->string('row')->nullable();
            $table->unsignedInteger('col')->nullable();
            $table->string('label')->index();
            $table->string('type')->default('regular');
            $table->enum('status', ['available','blocked','maintenance'])->default('available');
            $table->timestamps();
            $table->unique(['event_id','label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
