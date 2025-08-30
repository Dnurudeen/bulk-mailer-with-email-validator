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
        Schema::create('recipient_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('recipient_list_recipient', function (Blueprint $table) {
            $table->foreignId('recipient_list_id')->constrained('recipient_lists')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete(); // assumes recipients table exists
            $table->primary(['recipient_list_id', 'recipient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipient_list_recipient');
        Schema::dropIfExists('recipient_lists');
    }
};
