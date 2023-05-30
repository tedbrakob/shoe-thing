<?php

namespace App\StravaApi;

use App\Models\StravaAthlete;
use App\Services\StravaAthleteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

Class Auth {
    public static function getLoginUrl(): string
    {
        $clientId = config('strava.clientId');
        $redirectUri = config('strava.redirectUri');

        $baseUrl = 'http://www.strava.com/oauth/authorize';
        $query = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'approval_prompt' => 'force',
            'scope' => implode(',', [
                'profile:read_all',
                'activity:read_all',
                'activity:write',
            ]),
        ]);

        return "$baseUrl?$query";
    }

    public static function handleUserLogin($code): StravaAthlete
    {
        $data = static::exchangeToken($code);

        $athlete = StravaAthleteService::findOrCreateWithApiToken([
            'externalAthleteId' => $data->athlete->id,
            'accessToken' => $data->access_token,
            'refreshToken' => $data->refresh_token,
            'expiresAt' => new Carbon($data->expires_at),
        ]);

        StravaAthleteService::importShoes($athlete);

        return $athlete;
    }

    private static function exchangeToken(string $code)
    {
        $response = Http::post('https://www.strava.com/oauth/token', [
            'client_id' => config('strava.clientId'),
            'client_secret' => config('strava.clientSecret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        $body = $response->body();
        $data = json_decode($body);

        return $data;
    }
}
