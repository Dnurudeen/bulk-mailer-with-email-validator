<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailValidationBatch extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'stored_path',
        'total',
        'valid_count',
        'invalid_count',
        'status'
    ];

    public function results()
    {
        return $this->hasMany(EmailValidationResult::class, 'batch_id');
    }
}
