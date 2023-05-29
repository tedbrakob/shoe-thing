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
        Schema::create('strava_gears', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strava_athlete_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('external_strava_id');
            $table->string('name');
            $table->string('nickname');
            $table->unsignedSmallInteger('distance_meters');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('strava_gears');
    }
};
