<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnDurationSincePreviousMinutesByDurationSincePreviousSecondsToRotationLegs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rotation_legs', function (Blueprint $table) {
            $table->renameColumn('duration_since_previous_minutes', 'duration_since_previous_seconds');
        });

        Schema::table('rotations', function (Blueprint $table) {
            $table->renameColumn('duration_minutes', 'duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
