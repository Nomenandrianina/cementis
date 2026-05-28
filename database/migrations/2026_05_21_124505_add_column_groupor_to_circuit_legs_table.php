<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnGrouporToCircuitLegsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circuit_legs', function (Blueprint $table) {
            $table->string('group_or')->nullable()->after('optional');      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circuit_legs', function (Blueprint $table) {
            $table->dropColumn('group_or');
        });
    }
}
