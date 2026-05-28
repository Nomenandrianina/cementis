<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircuitCompletionRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circuit_completion_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('circuit_id')->constrained()->cascadeOnDelete();

            // Type de règle : 'or_end' = condition OU sur la fin
            //                 'zone_stay' = entré dans une zone + pas de sortie dans X heures
            $table->string('rule_type'); // 'or_end' | 'zone_stay'

            // Zone ou checkpoint déclencheur
            $table->string('reference_type'); // 'zone' | 'checkpoint'
            $table->unsignedBigInteger('reference_id');

            // Pour rule_type = 'zone_stay' : durée max de séjour sans sortie (en heures)
            // Si le véhicule est dans la zone sans sortir pendant cette durée → rotation fermée
            $table->unsignedInteger('stay_hours')->nullable();

            // Cette règle produit-elle une rotation valide ou incomplète ?
            $table->boolean('produces_valid_rotation')->default(false);

            // Label affiché dans les rapports
            $table->string('label')->nullable();

            $table->integer('order')->default(1);
            $table->timestamps();
        });

        // Ajouter sur circuit_legs : group_or déjà fait, ajouter is_start / is_end
        // pour identifier explicitement les legs de début et de fin
        Schema::table('circuit_legs', function (Blueprint $table) {
            $table->string('role')->nullable()->after('group_or');
            // 'start' = peut être un déclencheur de début de rotation (T1)
            // 'end'   = peut être un déclencheur de fin de rotation (T5)
            // null    = étape intermédiaire normale
        });

        // Ajouter sur rotations : rule utilisée pour clôturer
        Schema::table('rotations', function (Blueprint $table) {
            $table->string('completion_rule_type')->nullable()->after('invalidation_reason');
            // 'normal'     = fin normale (T5 atteint)
            // 'zone_stay'  = rotation clôturée par séjour prolongé dans zone parente
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circuit_completion_rules');
        Schema::table('circuit_legs', fn($t) => $t->dropColumn('role'));
        Schema::table('rotations',    fn($t) => $t->dropColumn('completion_rule_type'));
    }
}
