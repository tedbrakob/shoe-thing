<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StravaAthlete extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'external_strava_id',
    ];

    protected $casts = [
        'last_fetched_activity_datetime' => 'datetime',
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
        return $this->getActiveGearAt(Carbon::now());
    }

    public function getCurrentActiveGearUsage()
    {
        return $this->getActiveGearUsageAt(Carbon::now());
    }

    public function getActiveGearAt(string $dateTime)
    {
        $usage = $this->getActiveGearUsageAt($dateTime);

        return $usage->stravaGear ?? null;
    }

    public function getActiveGearUsageAt(string $dateTime)
    {
        $active = $this->stravaGearUsages()
            ->with('stravaGear')
            ->where('start_at', '<=', $dateTime)
            ->orderByDesc('start_at')
            ->first();

        if ($active === null) {
            return null;
        }

        if ($active->finish_at !== null && $active->finish_at <= $dateTime) {
            return null;
        }

        return $active;
    }

    public function getLatestGearUsage()
    {
        return $this->stravaGearUsages()
            ->orderByDesc('start_at')
            ->first();
    }

    public function hasActiveUsagesSinceLastFetchedActivity()
    {
        $latestUsage = $this->getCurrentActiveGearUsage();

        if ($latestUsage === null) {
            return false;
        }

        if ($latestUsage->start_at >= $this->last_fetched_activity_datetime) {
            return true;
        }

        if (
            $latestUsage->finish_at >= $this->activitiesLastFetchedAt
            || $latestUsage->finishAt === null
        ) {
            return true;
        }

        return false;
    }
}
