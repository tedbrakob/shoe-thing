<?php

namespace App\StravaApi;

use Illuminate\Http\Response;
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

    public function getActivities(array $options = [])
    {
        $query = !empty($options) ? http_build_query($options) : '';

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->accessToken",
        ])->get("https://www.strava.com/api/v3/athlete/activities?$query");

        $body = $response->body();
        $data = json_decode($body);

        return array_map(function ($activity) {
            return (object) [
                'id' => $activity->id,
                'startDate' => $activity->start_date,
                'gearId' => $activity->gear_id,
            ];
        }, $data);
    }

    public function updateActivity(int $activityId, array $values): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->accessToken",
        ])->put("https://www.strava.com/api/v3/activities/$activityId", $values);

        return $response->status() === Response::HTTP_OK;
    }
}
