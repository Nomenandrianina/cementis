<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotationLegsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rotation_legs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('circuit_leg_id')->constrained()->cascadeOnDelete();
            $table->timestamp('occurred_at');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->integer('duration_since_previous_minutes')->nullable();
            $table->json('raw_event')->nullable();
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
        Schema::dropIfExists('rotation_legs');
    }
}
