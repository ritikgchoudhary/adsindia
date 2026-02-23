<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'description',
        'level',
        'validity_days',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'level' => 'integer',
        'validity_days' => 'integer',
        'status' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('level');
    }
}
