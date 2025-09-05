<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = [
        'email',
        'status',
        'reason',
        'is_disposable',
        'is_role',
        'is_catch_all_tested'
    ];
}
