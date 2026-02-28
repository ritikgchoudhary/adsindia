<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerApplication extends Model
{
    protected $table = 'influencer_applications';

    protected $fillable = [
        'lead_key',
        'name',
        'email',
        'phone',
        'platforms',
        'data',
        'status',
        'admin_notes',
        'is_draft',
        'last_step',
        'submitted_at',
        'source_url',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'platforms' => 'array',
        'data' => 'array',
        'status' => 'integer',
        'is_draft' => 'boolean',
        'last_step' => 'integer',
        'submitted_at' => 'datetime',
    ];
}

