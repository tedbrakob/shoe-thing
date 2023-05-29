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
        Schema::create('strava_gear_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strava_gear_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->dateTime('start_at')->index();
            $table->dateTime('finish_at')->nullable()->index();
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
        Schema::dropIfExists('strava_gear_usages');
    }
};
