<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StravaGear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'strava_athlete_id',
        'external_strava_id',
        'name',
        'nickname',
        'distance_meters',
    ];

    public function stravaGearUsages()
    {
        return $this->hasMany(StravaGearUsage::class);
    }
}
