<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipSubscription extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
