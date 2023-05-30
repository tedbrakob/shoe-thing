<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAthleteActivities;
use App\Models\StravaAthlete;
use Illuminate\Console\Command;

class UpdateStravaActivitiesWithActiveShoes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strava:update-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches latest activities from strava and updates them with shoes that were active at the time of the activity';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $athletes = StravaAthlete::all();

        foreach ($athletes as $athlete) {

            UpdateAthleteActivities::dispatch($athlete)
                ->onQueue('activityUpdates');
        }

        return Command::SUCCESS;
    }
}
