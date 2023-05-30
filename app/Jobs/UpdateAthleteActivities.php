<?php

namespace App\Jobs;

use App\Models\StravaAthlete;
use App\Services\StravaAthleteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAthleteActivities implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $stravaAthlete;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StravaAthlete $stravaAthlete)
    {
        $this->stravaAthlete = $stravaAthlete;
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->stravaAthlete->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StravaAthleteService $stravaAthleteService)
    {
        $stravaAthleteService->updateAthleteActivities($this->stravaAthlete);
    }
}
