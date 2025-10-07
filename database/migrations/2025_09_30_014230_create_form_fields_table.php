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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            $table->string('label');
            $table->string('name');
            $table->enum('type', [
                'text','email','numeric','image','date','datetime',
                'textarea','select','radio','checkbox','phone','url'
            ]);

            $table->boolean('is_required')->default(false);
            $table->boolean('is_toggleable')->default(true);
            $table->boolean('is_hidden_by_default')->default(false);
            $table->boolean('show_in_scan')->default(true);
            $table->boolean('show_in_form')->default(true);

            $table->integer('sort_order')->default(0);
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();

            $table->text('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id','name']);
            $table->index(['event_id','sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
