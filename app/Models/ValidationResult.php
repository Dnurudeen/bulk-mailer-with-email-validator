<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationResult extends Model
{
    protected $casts = [
        'is_valid' => 'boolean',
        'free' => 'boolean',
        'role' => 'boolean',
        'disposable' => 'boolean',
        'accept_all' => 'boolean',
        'tag' => 'boolean',
        'checked_at' => 'datetime',
    ];

    protected $fillable = [
        'validation_batch_id',
        'email',
        'name',
        'is_valid',
        'score',
        'state',
        'reason',
        'domain',
        'free',
        'role',
        'disposable',
        'accept_all',
        'tag',
        'mx_record',
        'checked_at'
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ValidationBatch::class);
    }
}
