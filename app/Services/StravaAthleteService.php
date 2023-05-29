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
