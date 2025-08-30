<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValidationBatch extends Model
{
    protected $fillable = ['user_id', 'filename', 'path', 'total', 'valid_count', 'invalid_count', 'status'];

    public function results(): HasMany
    {
        return $this->hasMany(ValidationResult::class);
    }
}
