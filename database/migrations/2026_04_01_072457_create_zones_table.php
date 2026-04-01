<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('gps_zone_id')->nullable(); // ID depuis l'API GPS (ex: "6163")
            $table->string('name');
            $table->string('type')->default('zone'); // zone, origin, destination
            $table->string('color')->nullable();
            $table->text('vertices')->nullable(); // JSON des coordonnées GPS depuis l'API
            $table->string('role')->nullable(); // 'start', 'end', 'waypoint'
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('zones');
    }
}
