<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircuitLegsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circuit_legs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('circuit_id')->constrained()->cascadeOnDelete();
            $table->integer('order'); // Ordre de l'étape (1, 2, 3...)
            $table->string('label'); // Ex: "T1 - Arrivée Tamatave"
            $table->string('event_type'); // enter_zone | leave_zone | pass_checkpoint
            $table->string('reference_type'); // zone | checkpoint
            $table->unsignedBigInteger('reference_id'); // ID zone ou checkpoint
            $table->string('direction')->default('any'); // inbound | outbound | any
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
        Schema::dropIfExists('circuit_legs');
    }
}
