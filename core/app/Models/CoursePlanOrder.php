<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePlanOrder extends Model
{
    protected $fillable = [
        'user_id',
        'course_plan_id',
        'amount',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(CoursePlan::class, 'course_plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
