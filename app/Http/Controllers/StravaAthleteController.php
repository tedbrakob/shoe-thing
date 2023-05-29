<?php

namespace App\Http\Controllers;

use App\Models\StravaAthlete;
use App\Services\StravaAthleteService;
use Illuminate\Http\Request;

class StravaAthleteController extends Controller
{
    public function __construct(
        protected StravaAthleteService $stravaAthleteService
    )
    {}

    public function show($athleteId)
    {
        $athlete = StravaAthlete::with('stravaGears')
            ->find($athleteId);

        $activeGear = $this->stravaAthleteService->getCurrentActiveGear($athlete);
        $athlete->activeGearId = $activeGear->id ?? null;

        return $athlete;
    }

    public function update(Request $request, $athleteId)
    {
        $gearId = $request->input('activeGearId');
        if (empty($gearId)) {
            $gearId = null;
        }

        $this->stravaAthleteService->setActiveGear($athleteId, $gearId);
    }
}
