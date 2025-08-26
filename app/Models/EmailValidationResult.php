<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailValidationResult extends Model
{
    protected $fillable = ['batch_id', 'email', 'name', 'is_valid', 'reason'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(EmailValidationBatch::class, 'batch_id');
    }
}
