<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MailCampaign extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'html_body',
        'status',
        'scheduled_at',
        'batch_size'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'campaign_recipient')
            ->withPivot(['status', 'queued_at', 'sent_at', 'error'])
            ->withTimestamps();
    }

    public function scopeRunnable($q)
    {
        return $q->whereIn('status', ['scheduled', 'sending'])
            ->where(function ($qq) {
                $qq->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            });
    }
}
