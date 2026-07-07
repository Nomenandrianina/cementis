<?php

namespace App\Http\Controllers;

use App\DataTables\ImportInstallationDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateImportInstallationRequest;
use App\Http\Requests\UpdateImportInstallationRequest;
use App\Repositories\ImportInstallationRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use App\Imports\InstallationImport;
use App\Models\ImportNameInstallation;
use App\Models\Chauffeur;
use App\Models\ImportCalendar;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;
use Response;

class ImportInstallationController extends AppBaseController
{
    /** @var ImportInstallationRepository $importInstallationRepository*/
    private $importInstallationRepository;

    public function __construct(ImportInstallationRepository $importInstallationRepo)
    {
        $this->importInstallationRepository = $importInstallationRepo;
    }

    /**
     * Display a listing of the ImportInstallation.
     *
     * @param ImportInstallationDataTable $importInstallationDataTable
     *
     * @return Response
     */
    public function index(ImportInstallationDataTable $importInstallationDataTable, $id = null)
    {
        if ($id !== null) {
            return $this->detail_liste_importation($id, $importInstallationDataTable);
        }

        return $importInstallationDataTable->render('import_installations.index');
    }

    /**
     * Show the form for creating a new ImportInstallation.
     *
     * @return Response
     */
    public function create()
    {
        return view('import_installations.create');
    }

    

    /**
     * Store a newly created ImportInstallation in storage.
     *
     * @param CreateImportInstallationRequest $request
     *
     * @return Response
     */
    public function store(CreateImportInstallationRequest $request)
    {
        $input = $request->all();

        $importInstallation = $this->importInstallationRepository->create($input);

        Flash::success(__('messages.saved', ['model' => __('models/importInstallations.singular')]));

        return redirect(route('importInstallations.index'));
    }



    /**
     * Display the specified ImportInstallation.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $importInstallation = $this->importInstallationRepository->find($id);

        if (empty($importInstallation)) {
            Flash::error(__('messages.not_found', ['model' => __('models/importInstallations.singular')]));

            return redirect(route('importInstallations.index'));
        }

        return view('import_installations.show')->with('importInstallation', $importInstallation);
    }

    /**
     * Show the form for editing the specified ImportInstallation.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $importInstallation = $this->importInstallationRepository->find($id);

        if (empty($importInstallation)) {
            Flash::error(__('messages.not_found', ['model' => __('models/importInstallations.singular')]));

            return redirect(route('importInstallations.index'));
        }

        return view('import_installations.edit')->with('importInstallation', $importInstallation);
    }

    /**
     * Update the specified ImportInstallation in storage.
     *
     * @param int $id
     * @param UpdateImportInstallationRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateImportInstallationRequest $request)
    {
        $importInstallation = $this->importInstallationRepository->find($id);

        if (empty($importInstallation)) {
            Flash::error(__('messages.not_found', ['model' => __('models/importInstallations.singular')]));

            return redirect(route('importInstallations.index'));
        }

        $importInstallation = $this->importInstallationRepository->update($request->all(), $id);

        Flash::success(__('messages.updated', ['model' => __('models/importInstallations.singular')]));

        return redirect(route('importInstallations.index'));
    }

    /**
     * Remove the specified ImportInstallation from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $importInstallation = $this->importInstallationRepository->find($id);

        if (empty($importInstallation)) {
            Flash::error(__('messages.not_found', ['model' => __('models/importInstallations.singular')]));

            return redirect(route('importInstallations.index'));
        }

        $this->importInstallationRepository->delete($id);

        Flash::success(__('messages.deleted', ['model' => __('models/importInstallations.singular')]));

        return redirect(route('importInstallations.index'));
    }


    

     /**
      * jonny
     * affichage de la formulaire d'importation.
     *
     * @return Response
     */
    public function affichageImportation(){
        return view('import_installations.importation_excel');
    }

    public function duplicateDriversFromLastPlanning()
    {
        $new_planning = ImportCalendar::latest('id')->first();

        if (!$new_planning) {
            return redirect()->back()->with('error', 'Aucun planning trouvé.');
        }

        $hasDrivers = Chauffeur::where('id_planning', $new_planning->id)->exists();

        if ($hasDrivers) {
            return redirect()->back()->with('info', 'Des chauffeurs existent déjà pour ce planning.');
        }

        $lastPlanningWithDrivers = Chauffeur::where('id_planning', '!=', $new_planning->id)
            ->max('id_planning');

        if (!$lastPlanningWithDrivers) {
            return redirect()->back()->with('error', 'Aucun planning précédent avec chauffeurs trouvé.');
        }

        $lastDrivers = Chauffeur::where('id_planning', $lastPlanningWithDrivers)->get();

        foreach ($lastDrivers as $driver) {
            $newDriver = $driver->replicate();
            $newDriver->id_planning = $new_planning->id;
            $newDriver->save();
        }

        return redirect()->back()->with('success', count($lastDrivers) . ' chauffeur(s) dupliqué(s) avec succès pour le nouveau planning.');
    }

    
    /**
     * Importation fichier excel
     *
     * @param Request $request
     *
     * @return Response
     */
    public function import_data_installation(Request $request)
    {
         // Validation de l'extension du fichier
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('excel_file');

        // Récupération du nom du fichier excel
        $nomCompletFichierExcel = $file->getClientOriginalName();
        $nomFichier = pathinfo($nomCompletFichierExcel, PATHINFO_FILENAME);

        // Vérification si le fichier a déjà été importé
        $verification = ImportNameInstallation::where('name', $nomFichier)->first();

        // if ($verification != null) {
        //     return redirect()->back()->with('alert', 'Ce fichier a été déjà importé.');
        // }

        // Enregistrement du nom de l'importation
        $import_name = new ImportNameInstallation([
            "name" => $nomFichier,
            "observation" => "" // Ajouter une observation si nécessaire
        ]);
        $import_name->save();

        // Ajout du nom du fichier importer
        $import = new InstallationImport($nomFichier, $import_name->id);
        Excel::import($import, $file);

        // Récupérer les compteurs de succès et d'erreur
        $successCount = $import->getSuccessCount();
        $errorCount = $import->getErrorCount();
        $totalCount = $successCount + $errorCount;
        
        // Préparer le message d'importation
        $message = " {$successCount} lignes sur {$totalCount} ont été importées avec succès";
        if($errorCount >0){
            $message_text = "lignes n'ont pas été importées";
            if($errorCount == 1){
                $message_text = "ligne n'a pas été importée"; 
            }
            $message = " {$successCount} lignes sur {$totalCount} ont été importées avec succès, {$errorCount} " . $message_text;
        }
        if($errorCount == 0){
            
            $import_name->update(['observation' => $message ]);
        }
        
        Alert::success(__('Importation réussie'),$message);

        return redirect(route('importNameInstallations.index'));
    }

    public function generateErrorFile(array $errors)
    {
        $errorText = implode("\n", $errors);
        $fileName = "import_errors_" . time() . ".txt";
        $filePath = storage_path("app/{$fileName}");
        file_put_contents($filePath, $errorText);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Display a listing of the ImportExcel filtered by id.
     *
     * @param int $id
     * @param ImportInstallationDataTable $importExcelDataTable
     * @return Response
     */
    public function liste_importation($id, ImportInstallationDataTable $importExcelDataTable)
    {
        return $importExcelDataTable->with('id', $id)->render('import_installations.index');
    }
    

    
    /**
     * Show the form for creating a new ImportInstallation.
     *
     * @return Response
     */
    public function exportation_excel()
    {
        return view('export_general.export_generique');
    }

}
