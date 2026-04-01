<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('r_vehicules');
            $table->foreignId('circuit_id')->constrained();
            $table->timestamp('started_at');   // T1 - Arrivée départ (ex: Tamatave)
            $table->timestamp('completed_at')->nullable(); // T5 - Retour au départ
            $table->integer('duration_minutes')->nullable(); // T5 - T1 en minutes
            $table->string('status')->default('in_progress'); // in_progress | completed | cancelled
            $table->string('counted_month')->nullable(); // YYYY-MM pour la règle chevauchement
            $table->boolean('is_valid')->default(false);
            $table->text('invalidation_reason')->nullable();
            $table->json('raw_events')->nullable(); // Événements GPS bruts utilisés
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
        Schema::drop('rotations');
    }
}
