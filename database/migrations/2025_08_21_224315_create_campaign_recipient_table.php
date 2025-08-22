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
        Schema::create('campaign_recipient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_campaign_id')->constrained('mail_campaigns')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'queued', 'sent', 'failed'])->default('pending');
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();
            $table->unique(['mail_campaign_id', 'recipient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_recipient');
    }
};
