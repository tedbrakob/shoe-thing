<?php

namespace App\StravaApi;

use Illuminate\Support\Facades\Http;

class Strava
{
    private string $accessToken;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getShoes()
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->accessToken",
        ])->get('https://www.strava.com/api/v3/athlete');

        $body = $response->body();
        $data = json_decode($body);
        return array_map(function ($shoe) {
            $gear = (object) [
                'externalStravaId' => $shoe->id,
                'name' => $shoe->name,
                'nickname' => $shoe->nickname,
                'distanceMeters' => $shoe->distance,
            ];

            return $gear;
        }, $data->shoes);
    }

    public function getActivities()
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->accessToken",
        ])->get('https://www.strava.com/api/v3/athlete/activities');


        $body = $response->body();
        $data = json_decode($body);

        return $data;
    }
}
