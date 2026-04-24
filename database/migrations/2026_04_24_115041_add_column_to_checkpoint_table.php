<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCheckpointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkpoints', function (Blueprint $table) {
            $table->string('type')->default('control')->after('name');
        });

        Schema::table('circuit_legs', function (Blueprint $table) {
            $table->boolean('optional')->default(false)->after('direction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkpoints',  fn($t) => $t->dropColumn('type'));
        Schema::table('circuit_legs', fn($t) => $t->dropColumn('optional'));
    }
}
