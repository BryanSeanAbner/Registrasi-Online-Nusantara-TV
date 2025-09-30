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
        Schema::create('form_field_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields')->cascadeOnDelete();

            // Nilai serbaguna:
            // - text/number/email/phone/url: disimpan di 'value'
            // - select/radio/checkbox: bisa simpan string atau JSON di 'value_json'
            // - image/file path: simpan path/URL di 'value'
            $table->longText('value')->nullable();   // fleksibel, paling umum
            $table->text('value_json')->nullable();  // untuk multi-select/checkbox dsb
            $table->decimal('value_number', 20, 6)->nullable(); // untuk numeric agar bisa query range

            $table->timestamps();

            // Optimasi query export/pivot
            $table->index(['registration_id','field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_field_values');
    }
};
