<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTragetDurationMinutesByTargetDurationSecondsToRotationObjective extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rotation_objectives', function (Blueprint $table) {
            $table->renameColumn('target_duration_minutes', 'target_duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rotation_objectives', function (Blueprint $table) {
            //
        });
    }
}
