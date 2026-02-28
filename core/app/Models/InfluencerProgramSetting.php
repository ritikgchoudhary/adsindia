<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerProgramSetting extends Model
{
    protected $table = 'influencer_program_settings';

    protected $fillable = [
        'apk_path',
        'apk_url',
        'apk_version',
        'updated_by',
    ];
}

