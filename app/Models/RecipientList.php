<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RecipientList extends Model
{
    protected $fillable = ['user_id', 'name'];

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'recipient_list_recipient');
    }
}
