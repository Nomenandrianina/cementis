<?php

namespace App\Imports;

use App\Models\ImportInstallation;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Transporteur;
use App\Models\Chauffeur;
use App\Models\ChauffeurUpdate;
use App\Models\ImportInstallationError;
use App\Models\ImportNameInstallation;
use App\Models\Vehicule;
use App\Models\Installateur;
use App\Models\Installation;
use App\Models\VehiculeUpdate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;


class InstallationImport implements ToCollection, WithHeadingRow
{
    protected $name_file_excel;
    protected $import_name_id;
    protected $errors = [];
    protected $transporteurs = [];
    protected $successCount = 0;
    protected $errorCount = 0;


    public function __construct($name_file_excel, $import_name_id)
    {
        $this->name_file_excel = $name_file_excel;
        $this->import_name_id = $import_name_id;
    }

    private function getRowValue(array $row, string $key, $default = null) {
        return array_key_exists($key, $row) ? $row[$key] : $default;
    }

    public function collection(collection $rows){
        $selectedPlanning = DB::table('import_calendar')->latest('id')->value('id');
        foreach ($rows as $index => $rowCollection) {
            $numero_ligne = $index + 2;
            $row = $rowCollection->toArray();

            // Extraction sécurisée des données
            $transporteurNom = $this->getRowValue($row, 'transporteur');
            $adresse = $this->getRowValue($row, 'adresse');
            $tel = (string) $this->getRowValue($row, 'transporteur_telephone');
            $rfid = $this->getRowValue($row, 'rfid');
            $rfid_physique = $this->getRowValue($row, 'rfid_physique');
            $numero_badge = $this->getRowValue($row, 'numero_badge');
            $nom = $this->getRowValue($row, 'nom');
            $chauffeur_tel = (string) $this->getRowValue($row, 'chauffeur_telephone');
            $imei = (string) $this->getRowValue($row, 'imei');
            $immatriculation = $this->getRowValue($row, 'immatriculation');
            $description = $this->getRowValue($row, 'description');
            $matricule_tech = (string) $this->getRowValue($row, 'matricule_tech');
            $rawDateInstallation = $this->getRowValue($row, 'date_installation');

            $dateInstallation = !empty($rawDateInstallation) ? $this->excelDateToCarbon($rawDateInstallation) : null;

            // Vérifier et insérer le transporteur
            if (!empty($transporteurNom)) {
                if (!isset($this->transporteurs[$transporteurNom])) {
                    $transporteur = Transporteur::firstOrCreate(
                        ['nom' => $transporteurNom],
                        ['adresse' => $adresse, 'tel' => $tel]
                    );
                    $this->transporteurs[$transporteurNom] = $transporteur->id;
                } else {
                    $transporteur = Transporteur::find($this->transporteurs[$transporteurNom]);
                }
            } else {
                // Si $transporteurNom est null ou vide, on ne fait rien
                // (et surtout, on ne crée pas de transporteur vide)
                $transporteur = null;
            }

            // Vérifier chauffeur et véhicule
            $existingChauffeur = !empty($nom) ? Chauffeur::where('nom', $nom)->where('id_planning', $selectedPlanning)->first() : null;
        

            if (!$existingChauffeur) {
                $chauffeur = Chauffeur::create([
                    'rfid'            => $rfid,
                    'nom'             => $nom,
                    'rfid_physique'   => $rfid_physique,
                    'numero_badge'    => $numero_badge,
                    'contact'         => $chauffeur_tel,
                    'transporteur_id' => $transporteur?->id,
                    'id_planning'     => $selectedPlanning,
                ]);
            } else {
                $chauffeur = $existingChauffeur;
            }

            // Installateur
            $installateur = Installateur::firstOrCreate(
                ['matricule' => $matricule_tech],
                ['obs' => ""]
            );

            // ImportInstallation
            ImportInstallation::create([
                'transporteur_nom'        => $transporteurNom,
                'transporteur_adresse'    => $adresse,
                'transporteur_tel'        => $tel,
                'chauffeur_nom'           => $nom,
                'chauffeur_rfid'          => $rfid,
                'chauffeur_contact'       => $chauffeur_tel,
                'vehicule_nom'            => $immatriculation,
                'vehicule_imei'           => $imei,
                'vehicule_description'    => $description,
                'installateur_matricule' => $matricule_tech,
                'dates'                   => $dateInstallation,
                'import_name_id'          => $this->import_name_id,
            ]);

            $this->successCount++;
        }
        $this->saveErrors();
    }



    protected function addError($error)
    {
        $this->errors[] = $error . "";
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function saveErrors()
    {
        if (!empty($this->errors)) {
            ImportInstallationError::create([
                'name' => 'Importation Erreur',
                'contenu' => implode("<br>", $this->errors),
                'import_name_id' => $this->import_name_id,
            ]);
            
            $importationinstall = ImportNameInstallation::find($this->import_name_id);
            // $formattedErrors = implode("<br>", array_map(function ($error) {
            //     return nl2br("Ligne N° " . $error); // Ajoute "Ligne N°" à chaque ligne avec un saut de ligne HTML
            // }, $this->errors));

            $importationinstall->update(['observation' => implode("", $this->errors)]);

        }
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    protected function excelDateToCarbon($excelDate)
    {
        if (is_numeric($excelDate)) {
            // Excel date starts on 1st January 1900
            return Carbon::createFromDate(1900, 1, 1)->addDays($excelDate - 2); // Excel's day 1 is actually 0
        } else {
            // Si ce n'est pas un nombre, essayer de le convertir avec Carbon
            return null;
        }
    }


    // public function generateErrorFile()
    // {
    //     if (empty($this->errors)) {
    //         return null; // Pas d'erreurs, pas de fichier à générer
    //     }

    //     $errorText = implode("\n", $this->errors);
    //     $fileName = "import_errors_" . time() . ".txt";

    //     // Créer un nom de fichier unique
    //     // $fileName = "export_" . date('Ymd_His') . ".txt";

    //     // Enregistrer le contenu dans un fichier texte temporaire
    //     $filePath = storage_path("app/{$fileName}");
    //     file_put_contents($filePath, $errorText);

    //     // Retourner une réponse de téléchargement
    //     return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        
    // }


}
