<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialDiscountLink extends Model
{
    protected $table = 'special_discount_links';

    protected $fillable = [
        'package_id',
        'discount',
        'sig',
        'is_global',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'discount' => 'integer',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'created_by' => 'integer',
    ];

    public $timestamps = false;
}

