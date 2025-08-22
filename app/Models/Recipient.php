<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recipient extends Model
{
    protected $fillable = ['email', 'name'];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(MailCampaign::class, 'campaign_recipient')
            ->withPivot(['status', 'queued_at', 'sent_at', 'error'])
            ->withTimestamps();
    }
}
