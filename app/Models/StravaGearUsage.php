<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StravaGearUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'strava_gear_id',
        'start_at',
    ];

    public function stravaGear()
    {
        return $this->belongsTo(StravaGear::class);
    }
}
