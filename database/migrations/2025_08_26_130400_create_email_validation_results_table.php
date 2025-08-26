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
        Schema::create('email_validation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('email_validation_batches')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->string('reason')->nullable(); // e.g. syntax, no_mx, api_fail, disposable, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_validation_results');
    }
};
