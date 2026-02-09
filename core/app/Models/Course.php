<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'video_url',
        'duration',
        'students_count',
        'price',
        'required_package_id',
        'category',
        'affiliate_commission_percent',
        'affiliate_commission_fixed',
        'is_recommended',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'affiliate_commission_percent' => 'decimal:2',
        'affiliate_commission_fixed' => 'decimal:2',
        'is_recommended' => 'boolean',
        'status' => 'integer',
        'students_count' => 'integer',
        'required_package_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get courses that are active
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get courses ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }
}
