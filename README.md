## Shoe-Thing

Shoe-thing is a way to automate assigning shoes to Strava activites via a check-out, check-in process.  Rather than manually assigning shoes via the Strava app after completing an activity, the shoe-thing API can be used to check out a pair of shoes that will automatically be assigned to each activity until they are checked back in, or until another pair is checked out.

## Development

Shoe-thing uses [Laravel Sail](https://laravel.com/docs/10.x/sail) to run a development environment with Docker. Install Docker Desktop before continuing.

Execute the commands below from within the project directory to run the app server:

```
cp .example.env .env
composer install
php artisan key:generate
./vendor/bin/sail up -d
```

After this, be sure to add your Strava api key and secret to the following lines in `.env`
```
STRAVA_CLIENT_ID=
STRAVA_CLIENT_SECRET=
```

## Queues and Scheduled Commands
Sail has been configured in this project to run Horizon and the Artisan command scheduler in the background.  Horizon will use Redis as its queue storage.

## API Routes
* `GET "/api/strava-login"`
    * Generates and redirects to a URL for a Strava app authorization page.
---
* `GET "/api/strava-exchange-token?code={code}"`
    * Handles the redirect from the Strava authorization page.
    * On new login, populates database with new StravaAthlete and StravaApiToken records.
    * Updates shoe list with StravaGear records.
---
* `GET "/api/athletes/{athleteId}"`
    * Returns the requested Athlete's data including shoes
---
* `PATCH "/api/athletes/{athleteId}"`
    * Accepts `Content-Type: application/x-www-form-urlencoded` with one field of `activeGearId`
    * `activeGearId` is a numerical id from a StravaGear resource.
        * passing an ID will "check-out" that pair of shoes if it belongs to the athlete specified in the URL.
        * passing a value of `0` will "check-in" the last pair of "checked-out" shoes for that athlete.


## Artisan Commands
* `strava:update-activities`
    * Scheduled to run every minute
    * Dispatches an `UpdateAthleteActivities` job for each existing athlete onto the `activityUpdates` queue.

## Jobs
* `UpdateAthleteActivities`
    * Fetches all Strava activities for the specified athlete from after the last update.
        * The "last update" refers to the time of the last activity that was fetched, not the last time we checked for new activities.  This is necessary because we are checking against the activity's *Start* time which will be in the past, usually before the last check was made.
    * Assigns shoes to each Strava activity based on which shoes were "checked-out" at the time of the activity

## Todo
* Implement oauth refresh token process
    * The Strava api tokens are short-lived and require refreshing.  Add this to `App\StravaApi\Strava`.  Extract repeated Http request code into its own function and check the token expiration (already stored in db), refreshing if needed.

* Implement User->Athlete relationship and add authentication
    * Each StravaAthlete should belong to a user which could then interact with the API routes via an access token rather than directly passing the athlete id.  As is, this app should only be hosted locally as there is no authentication or authorization.

* Endpoint parameter validation
    * The API endpoints do not behave well if parameters are missing or in the wrong format

* Implement Strava webhooks endpoint
    * This was intentionally omitted to make use of scheduled commands.
    * The Strava API allows apps to implement a webhook subscription to receive athlete activities.  This would eliminate the need to poll for activities in a scheduled command.
