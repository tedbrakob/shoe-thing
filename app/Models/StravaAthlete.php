<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StravaAthlete extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_strava_id',
    ];

    public function stravaApiToken()
    {
        return $this->hasOne(StravaApiToken::class);
    }

    public function stravaGears()
    {
        return $this->hasMany(StravaGear::class);
    }

    public function stravaGearUsages()
    {
        return $this->hasManyThrough(StravaGearUsage::class, StravaGear::class);
    }

    public function getCurrentActiveGear()
    {
        $active = $this->getCurrentActiveGearUsage();

        return $active->stravaGear ?? null;
    }

    public function getCurrentActiveGearUsage()
    {
        $active = $this->stravaGearUsages()
            ->with('stravaGear')
            ->orderByDesc('start_at')
            ->first();

        if ($active->finish_at !== null && $active->finish_at < Carbon::now()) {
            return null;
        }

        return $active;
    }
}
