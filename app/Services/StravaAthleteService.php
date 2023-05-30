<?php

namespace App\Services;

use App\Models\StravaAthlete;
use App\Models\StravaGear;
use App\Models\StravaGearUsage;
use App\StravaApi\Strava;
use Carbon\Carbon;

class StravaAthleteService {
    public static function findOrCreateWithApiToken(array $options): StravaAthlete
    {
        $athlete = StravaAthlete::firstOrCreate(
            ['external_strava_id' => $options['externalAthleteId']]
        );

        $athlete->stravaApiToken()->updateOrCreate([], [
            'access_token' => $options['accessToken'],
            'refresh_token' => $options['refreshToken'],
            'expires_at' => $options['expiresAt'],
        ]);

        return $athlete;
    }

    public static function updateAthleteActivities(StravaAthlete $stravaAthlete)
    {
        if (!$stravaAthlete->hasActiveUsagesSinceLastFetchedActivity()) {
            return;
        }

        $activities = static::getAthleteActivitiesSinceLastFetched($stravaAthlete);

        $maxActivityStart = '';
        foreach ($activities as $activity) {
            $maxActivityStart = max($maxActivityStart, $activity->startDate);

            if ($activity->gearId !== null) {
                //do not overwrite if gear is already set
                continue;
            }

            $activeGear = $stravaAthlete->getActiveGearAt($activity->startDate);

            if ($activeGear === null) {
                continue;
            }

            static::updateActivityGear($stravaAthlete, $activity->id, $activeGear->external_strava_id);
        }

        if ($maxActivityStart === '') {
            return;
        }

        $stravaAthlete->last_fetched_activity_datetime = $maxActivityStart;
        $stravaAthlete->save();
    }

    public static function importShoes(StravaAthlete $athlete): void
    {
        $stravaApi = new Strava($athlete->stravaApiToken->access_token);
        $shoes = $stravaApi->getShoes();

        foreach ($shoes as $shoe) {
            $athlete->stravaGears()->updateOrCreate([
                'strava_athlete_id' => $athlete->id,
                'external_strava_id' => $shoe->externalStravaId,
            ], [
                'name' => $shoe->name,
                'nickname' => $shoe->nickname,
                'distance_meters' => $shoe->distanceMeters,
            ]);
        }
    }

    public static function setActiveGear(int $athleteId, ?int $gearId)
    {
        if ($gearId === null) {
            static::unsetActiveGear($athleteId);
            return;
        }

        $gear = StravaGear::whereStravaAthleteId($athleteId)
            ->whereId($gearId)
            ->firstOrFail();

        StravaGearUsage::create([
            'strava_gear_id' => $gear->id,
            'start_at' => Carbon::now(),
        ]);
    }

    private static function getAthleteActivitiesSinceLastFetched(StravaAthlete $athlete)
    {
        $stravaApi = new Strava($athlete->stravaApiToken->access_token);
        $lastFetchedTimestamp = strtotime($athlete->last_fetched_activity_datetime);

        $activities = $stravaApi->getActivities(['after' => $lastFetchedTimestamp]);
        return $activities;
    }

    private static function updateActivityGear(StravaAthlete $athlete, int $activityId, string $gearStravaId)
    {
        $stravaApi = new Strava($athlete->stravaApiToken->access_token);
        return $stravaApi->updateActivity($activityId, ['gear_id' => $gearStravaId]);
    }

    private static function unsetActiveGear(int $athleteId)
    {
        $athlete = StravaAthlete::find($athleteId);
        $active = $athlete->getCurrentActiveGearUsage();

        if (empty($active)) {
            return;
        }

        $active->finish_at = Carbon::now();
        $active->save();
    }
}
