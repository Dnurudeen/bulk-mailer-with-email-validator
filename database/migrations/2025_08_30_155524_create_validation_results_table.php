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
        Schema::create('validation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_batch_id')->constrained('validation_batches')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->boolean('is_valid')->nullable();
            $table->integer('score')->nullable();
            $table->string('state')->nullable();
            $table->string('reason')->nullable();
            $table->string('domain')->nullable();
            $table->boolean('free')->nullable();
            $table->boolean('role')->nullable();
            $table->boolean('disposable')->nullable();
            $table->boolean('accept_all')->nullable();
            $table->boolean('tag')->nullable();
            $table->string('mx_record')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_results');
    }
};
