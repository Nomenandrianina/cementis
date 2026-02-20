<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoreDriverTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_driver', function (Blueprint $table) {
            $table->increments('id');
            $table->string('badge')->nullable();
            $table->decimal('score')->nullable();
            $table->integer('id_planning')->nullable();
            $table->integer('transporteur')->nullable();
            $table->longText('observation')->nullable();
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
        Schema::drop('score_driver');
    }
}
