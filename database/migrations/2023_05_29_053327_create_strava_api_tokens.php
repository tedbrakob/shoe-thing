<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('strava_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strava_athlete_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('access_token');
            $table->string('refresh_token');
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('strava_api_tokens');
    }
};
