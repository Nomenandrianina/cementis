<?php

use App\Http\Controllers\CheckpointController;
use App\Http\Controllers\CircuitController;
use App\Http\Controllers\RotationObjectiveController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RotationController;
use App\Http\Controllers\RvehiculeController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;


Route::get('/', [
    App\Http\Controllers\DashboardController::class, 'index'
])->name('dashboard');

/* Routes ajoutés */
/* Route Parametre importation */
Route::post('fileUploads/file-read', [App\Http\Controllers\FileUploadController::class, 'read'])->name('fileUploads.read');
Route::get('fileUploads/parametre', [App\Http\Controllers\FileUploadController::class, 'parametre'])->name('fileUploads.parametre');
Route::get('fileUploads/get-fillable-fields/{model}', [App\Http\Controllers\FileUploadController::class, 'getFillableFields'])->name('fileUploads.getFillableFields');
Route::get('fileUploads/get-models/{model}', [App\Http\Controllers\FileUploadController::class, 'getModels'])->name('fileUploads.getModels');
Route::get('fileUploads/get-associations/{modelId}', [App\Http\Controllers\FileUploadController::class, 'getAssociations'])->name('fileUploads.getAssociations');
/* ----------------------------------------------------------------------------------------------------------------------------- */

Route::resource('permissions', App\Http\Controllers\PermissionController::class);
Route::post('permissions/loadFromRouter', [App\Http\Controllers\PermissionController::class, 'LoadPermission'])->name('permissions.load-router');

Route::resource('roles', App\Http\Controllers\RoleController::class);

Route::get('profile', [App\Http\Controllers\UserController::class, 'showProfile'])->name('users.profile');
Route::patch('profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('users.updateProfile');

Route::resource('users', App\Http\Controllers\UserController::class);


Route::resource('attendances', App\Http\Controllers\AttendanceController::class);

Route::get('generator_builder', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@builder')->name('generator_builder.index');

Route::get('field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@fieldTemplate')->name('generator_builder.field_template');

Route::get('relation_field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@relationFieldTemplate')->name('generator_builder.relation_field_template');

Route::post('generator_builder/generate', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generate')->name('generator_builder.generate');

Route::post('generator_builder/rollback', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@rollback')->name('generator_builder.rollback');

Route::post(
    'generator_builder/generate-from-file',
    '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generateFromFile'
)->name('generator_builder.from_file');


Route::resource('fileUploads', App\Http\Controllers\FileUploadController::class);

Route::resource('messages', App\Http\Controllers\MessageController::class);

Route::resource('rotations', App\Http\Controllers\RotationController::class);

Route::resource('parametres', App\Http\Controllers\ParametreController::class);

Route::resource('penalites', App\Http\Controllers\PenaliteController::class);

Route::resource('importExcels', App\Http\Controllers\ImportExcelController::class);

Route::get('/import-affichage', 'App\Http\Controllers\ImportExcelController@affichage_import')->name('import.affichage');

Route::post('/import-excel', 'App\Http\Controllers\ImportExcelController@import_excel')->name('import.excel');

Route::post('import/driver/excel', 'App\Http\Controllers\ChauffeurController@import_driver_excel')->name('import.driver.excel');

Route::get('/import-liste', 'App\Http\Controllers\ImportExcelController@liste_importation')->name('import.liste');

Route::get('import-excels/detail/{id}', 'App\Http\Controllers\ImportExcelController@detail_liste_importation')->name('import_excels.detail_liste_importation');

Route::get('incident_vehicule/detail/{id}', 'App\Http\Controllers\IncidentVehiculeCoordonneeController@detail_liste_coordonnee')->name('incident_vehicule.detail');

Route::resource('importcalendars', App\Http\Controllers\ImportcalendarController::class);

Route::get('/import-installation-affichage', 'App\Http\Controllers\ImportInstallationController@affichageImportation')->name('import.installation.affichage');

Route::post('/import-installation', 'App\Http\Controllers\ImportInstallationController@import_data_installation')->name('import.installation.store');

Route::get('import-excels/installation/{id}', 'App\Http\Controllers\ImportInstallationController@liste_importation')->name('import_excels.installation');

Route::get('/exportation-generale', 'App\Http\Controllers\ExportController@exportation_excel')->name('exportation.view');

Route::post('/getcolumns', 'App\Http\Controllers\ExportController@getTableColumns')->name('exportation.getcolumns');

Route::post('/exportation-table', 'App\Http\Controllers\ExportController@exportTable')->name('exportation.getexport');

Route::resource('chauffeurs', App\Http\Controllers\ChauffeurController::class);

Route::post('chauffeur/updatetransporteur', 'App\Http\Controllers\ChauffeurController@update_tranporteur_id')->name('chauffeur.updatetransporteur');

Route::post('chauffeur/deleteSending', 'App\Http\Controllers\ChauffeurController@delete_sending')->name('chauffeur.deleteSending');

Route::post('chauffeur/filtre', 'App\Http\Controllers\TransporteurController@filterChauffeurs')->name('chauffeur.filtre');

Route::resource('penaliteChauffeurs', App\Http\Controllers\PenaliteChauffeurController::class);

Route::get('/scoring/{chauffeur}', 'App\Http\Controllers\ImportExcelController@associateEventWithCalendar')->name('scoring.monthly');

Route::get('events/scoring', 'App\Http\Controllers\EventController@viewScoring')->name('events.scoring');

Route::get('/event/routes', 'App\Http\Controllers\EventController@getRoutes')->name('event.routes');

Route::get('/export/excel/detail/scoring/{imei}/{badge}/{id_planning}', 'App\Http\Controllers\ScoringController@export_excel_driver_Scoring')->name('export.excel.detail.scoring');

Route::get('/export/excel/scoring', 'App\Http\Controllers\ScoringController@export_excel_scoring_card')->name('export.excel.scoring');

Route::post('/save-comments', [App\Http\Controllers\ScoringController::class, 'saveComments'])->name('save.comments');

Route::get('/new/scoring', 'App\Http\Controllers\ScoringController@scoring_card')->name('new.scoring');

Route::get('/ajax/scoring', 'App\Http\Controllers\ScoringController@filter_scoring_by_planning')->name('ajax.scoring');

// ---------------------------------------------------------X---------------------------------------------------------

Route::get('/driver/score', 'App\Http\Controllers\ScoreDriverController@score_driver')->name('driver.score');

Route::get('/driver/score/filter/planning', 'App\Http\Controllers\ScoreDriverController@filter_score_drive_by_planning')->name('driver.score.filter.planning');

Route::get('/driver/score/excel', 'App\Http\Controllers\ScoreDriverController@export_excel_score_drive')->name('driver.score.excel');

Route::get('/driver/score/detail/{badge}/{id_planning}', 'App\Http\Controllers\ScoreDriverController@detail_score_drive')->name('driver.score.detail');

Route::get('/driver/detail/score/zero', 'App\Http\Controllers\ScoreDriverController@detail_score_driver_zero')->name('driver.detail.score.zero');

Route::get('/driver/detail/score/zero/more/than/3/plannings', 'App\Http\Controllers\ScoreDriverController@detail_score_driver_zero_more_than_3_planning')->name('driver.detail.score.zero.more.than.3.plannings');

// ---------------------------------------------------------X---------------------------------------------------------

Route::get('/ajax/scoringdriver', 'App\Http\Controllers\ScoringController@FilterByTruckInCalendar')->name('ajax.scoringdriver');

Route::get('/detail/driver-match-rfid', 'App\Http\Controllers\ScoringController@driver_match_rfid')->name('detail.driver-match-rfid');

Route::get('/detail/driver-has-scoring', 'App\Http\Controllers\ScoringController@driver_has_scoring')->name('detail.driver-has-scoring');

Route::get('/detail/driver-have-not-scoring', 'App\Http\Controllers\ScoringController@driver_have_not_scoring')->name('detail.driver-have-not-scoring');

Route::get('/detail/truck-have-not-scoring', 'App\Http\Controllers\VehiculeController@count_driver_not_has_scoring')->name('detail.truck-have-not-scoring');

Route::get('/detail/truck-calendar', 'App\Http\Controllers\VehiculeController@count_car_in_calendar')->name('detail.truck-calendar');

Route::get('/detail/badge-calendar', 'App\Http\Controllers\ImportExcelController@count_badge_in_calendar')->name('detail.badge-calendar');

Route::get('/driver/detail/scoring/{imei}/{badge}/{id_planning}', 'App\Http\Controllers\ScoringController@driver_detail_scoring')->name('driver.detail.scoring');

Route::get('/truck/detail/scoring/{vehicule}/{id_planning}', 'App\Http\Controllers\ScoringController@truck_detail_scoring')->name('truck.detail.scoring');

Route::get('/scoring/pdf', 'App\Http\Controllers\EventController@TableauScoringPdf')->name('scoring.pdf');

Route::get('/chauffeurs/edit_story/{id}', 'App\Http\Controllers\ChauffeurController@edit_story')->name('chauffeurs.edit_story');

Route::resource('events', App\Http\Controllers\EventController::class);

Route::resource('transporteurs', App\Http\Controllers\TransporteurController::class);


Route::resource('groupeEvents', App\Http\Controllers\GroupeEventController::class);

Route::resource('vehicules', App\Http\Controllers\VehiculeController::class);

Route::resource('infractions', App\Http\Controllers\InfractionController::class);


Route::resource('scorings', App\Http\Controllers\ScoringController::class);


Route::resource('installateurs', App\Http\Controllers\InstallateurController::class);


Route::resource('installations', App\Http\Controllers\InstallationController::class);


Route::resource('importInstallations', App\Http\Controllers\ImportInstallationController::class);


Route::resource('importNameInstallations', App\Http\Controllers\ImportNameInstallationController::class);


Route::resource('importInstallationErrors', App\Http\Controllers\ImportInstallationErrorController::class);


Route::resource('movements', App\Http\Controllers\MovementController::class);

Route::resource('importModels', App\Http\Controllers\ImportModelController::class);

Route::resource('process', App\Http\Controllers\ProcessController::class);


Route::resource('periodSettings', App\Http\Controllers\PeriodSettingController::class);


Route::resource('chauffeurUpdateTypes', App\Http\Controllers\ChauffeurUpdateTypeController::class);


Route::resource('chauffeurUpdateStories', App\Http\Controllers\ChauffeurUpdateStoryController::class);


Route::post('/chauffeurUpdateStorie/validation', 'App\Http\Controllers\ChauffeurUpdateStoryController@ValidationUpdateChauffeur')->name('chauffeurUpdateStorie.validation');

Route::get('/chauffeurUpdateStorie/validation_list', 'App\Http\Controllers\ChauffeurUpdateStoryController@validation_list')->name('chauffeurUpdateStorie.validation_list');

Route::post('/validationRequest/validation', 'App\Http\Controllers\ValidationController@ValidationRequestChauffeur')->name('validationRequest.creation');

Route::get('/incident/index', 'App\Http\Controllers\IncidentController@index')->name('incident.index');

Route::resource('incidentVehicules', App\Http\Controllers\IncidentVehiculeController::class);


Route::resource('incidentVehicules', App\Http\Controllers\IncidentVehiculeController::class);


Route::resource('incidentVehicules', App\Http\Controllers\IncidentVehiculeController::class);


Route::resource('incidentVehiculeCoordonnees', App\Http\Controllers\IncidentVehiculeCoordonneeController::class);


Route::resource('scoreDrivers', App\Http\Controllers\ScoreDriverController::class);

Route::get('/infractions/upload/file', 'App\Http\Controllers\InfractionController@showImportForm')->name('infractions.upload.file');

Route::post('/infractions/import', 'App\Http\Controllers\InfractionController@import')->name('infractions.import');

Route::get('/events/upload/file', 'App\Http\Controllers\EventController@showImportForm')->name('events.upload.file');

Route::post('/events/import', 'App\Http\Controllers\EventController@import')->name('events.import');

 
// ── Véhicules ──────────────────────────────────────────────────────────────
Route::prefix('vehicles')->name('vehicles.')->group(function () {
    Route::get('/',         [RvehiculeController::class, 'index'])->name('index');
    Route::post('/sync',    [RvehiculeController::class, 'sync'])->name('sync');
    Route::patch('/{vehicle}/toggle', [RvehiculeController::class, 'toggle'])->name('toggle');
});
 
// ── Zones ──────────────────────────────────────────────────────────────────
Route::prefix('zones')->name('zones.')->group(function () {
    Route::get('/',               [ZoneController::class, 'index'])->name('index');
    Route::post('/sync',          [ZoneController::class, 'sync'])->name('sync');
    Route::post('/',              [ZoneController::class, 'store'])->name('store');
    Route::put('/{zone}',         [ZoneController::class, 'update'])->name('update');
    Route::delete('/{zone}',      [ZoneController::class, 'destroy'])->name('destroy');
});
 
// ── Checkpoints ────────────────────────────────────────────────────────────
Route::prefix('checkpoints')->name('checkpoints.')->group(function () {
    Route::get('/',                       [CheckpointController::class, 'index'])->name('index');
    Route::post('/sync',                  [CheckpointController::class, 'sync'])->name('sync');
    Route::post('/',                      [CheckpointController::class, 'store'])->name('store');
    Route::put('/{checkpoint}',           [CheckpointController::class, 'update'])->name('update');
    Route::delete('/{checkpoint}',        [CheckpointController::class, 'destroy'])->name('destroy');
});
 
// ── Circuits ───────────────────────────────────────────────────────────────
Route::prefix('circuits')->name('circuits.')->group(function () {
    Route::get('/',          [CircuitController::class, 'index'])->name('index');
    Route::get('/create',    [CircuitController::class, 'create'])->name('create');
    Route::post('/',         [CircuitController::class, 'store'])->name('store');
    Route::get('/{circuit}/edit', [CircuitController::class, 'edit'])->name('edit');
    Route::put('/{circuit}', [CircuitController::class, 'update'])->name('update');
    Route::delete('/{circuit}', [CircuitController::class, 'destroy'])->name('destroy');
 
    // Étapes du circuit
    Route::post('/{circuit}/legs',                         [CircuitController::class, 'storeLeg'])->name('legs.store');
    Route::put('/{circuit}/legs/{leg}',                    [CircuitController::class, 'updateLeg'])->name('legs.update');
    Route::delete('/{circuit}/legs/{leg}',                 [CircuitController::class, 'destroyLeg'])->name('legs.destroy');
    Route::post('/{circuit}/legs/reorder',                 [CircuitController::class, 'reorderLegs'])->name('legs.reorder');
 
    // Véhicules affectés
    Route::post('/{circuit}/vehicles',                     [CircuitController::class, 'assignVehicle'])->name('vehicles.assign');
    Route::delete('/{circuit}/vehicles/{vehicle}',         [CircuitController::class, 'removeVehicle'])->name('vehicles.remove');
 
    // Objectifs
    Route::get('/{circuit}/objectives',                    [RotationObjectiveController::class, 'index'])->name('objectives.index');
    Route::post('/{circuit}/objectives',                   [RotationObjectiveController::class, 'store'])->name('objectives.store');
    Route::put('/{circuit}/objectives/{objective}',        [RotationObjectiveController::class, 'update'])->name('objectives.update');
    Route::delete('/{circuit}/objectives/{objective}',     [RotationObjectiveController::class, 'destroy'])->name('objectives.destroy');
});
 
// ── Rotations ──────────────────────────────────────────────────────────────
Route::prefix('rotations')->name('rotations.')->group(function () {
    Route::get('/',                   [RotationController::class, 'index'])->name('index');
    Route::get('/{rotation}',         [RotationController::class, 'show'])->name('show');
    Route::post('/calculate',         [RotationController::class, 'calculate'])->name('calculate');
    Route::delete('/{rotation}',      [RotationController::class, 'destroy'])->name('destroy');
});
 
// ── Rapports ───────────────────────────────────────────────────────────────
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/',          [ReportController::class, 'index'])->name('index');
    Route::get('/monthly',   [ReportController::class, 'monthly'])->name('monthly');
    Route::get('/export-csv',[ReportController::class, 'exportCsv'])->name('export_csv');
    Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export_excel');
});