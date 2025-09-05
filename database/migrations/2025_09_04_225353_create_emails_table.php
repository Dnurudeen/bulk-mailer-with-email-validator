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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('status', ['pending', 'valid', 'invalid', 'catch-all', 'unknown'])->default('pending');
            $table->text('reason')->nullable();
            $table->boolean('is_disposable')->default(false);
            $table->boolean('is_role')->default(false);
            $table->boolean('is_catch_all_tested')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
