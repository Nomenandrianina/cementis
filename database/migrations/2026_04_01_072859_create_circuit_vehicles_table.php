<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircuitVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circuit_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('circuit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicule_id')->constrained('r_vehicules')->cascadeOnDelete();
            $table->date('assigned_from');
            $table->date('assigned_until')->nullable();
            $table->timestamps();
            $table->unique(['circuit_id', 'vehicule_id', 'assigned_from']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circuit_vehicles');
    }
}
