<?php

namespace App\Imports;

use App\Models\Infraction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InfractionImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
    * @param Collection $rows
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    // public function collection(Collection $rows)
    // {
    //     $data = [];

    //     foreach ($rows as $row) {
    //         $data[] = [
    //             'imei' => $row['imei'],
    //             'rfid' => $row['rfid'],
    //             'vehicule' => $row['vehicule'],
    //             'event' => $row['event'],
    //             'duree_infraction' => $row['duree_infraction'],
    //             'date_debut' => is_numeric($row['date_debut'])
    //             ? Date::excelToDateTimeObject($row['date_debut'])->format('Y-m-d')
    //             : $row['date_debut'],

    //             'heure_debut' => is_numeric($row['heure_debut'])
    //                 ? Date::excelToDateTimeObject($row['heure_debut'])->format('H:i:s')
    //                 : $row['heure_debut'],

    //             'date_fin' => is_numeric($row['date_fin'])
    //                 ? Date::excelToDateTimeObject($row['date_fin'])->format('Y-m-d')
    //                 : $row['date_fin'],

    //             'heure_fin' => is_numeric($row['heure_fin'])
    //                 ? Date::excelToDateTimeObject($row['heure_fin'])->format('H:i:s')
    //                 : $row['heure_fin'],
    //             'point' => 1,
    //             'duree_initial' => 60,
    //         ];
    //     }

    //     DB::table('infractions')->insert($data);
    // }
    public function model(array $row)
    {
        return new Infraction([
            'imei'             => $row['imei'],
            'rfid'             => $row['rfid'],
            'vehicule'         => $row['vehicule'],
            'event'            => $row['event'],
            'duree_infraction' => $row['duree_infraction'],
            'date_debut'       => is_numeric($row['date_debut'])
                                    ? Date::excelToDateTimeObject($row['date_debut'])->format('Y-m-d')
                                    : $row['date_debut'],
            'heure_debut'      => is_numeric($row['heure_debut'])
                                    ? Date::excelToDateTimeObject($row['heure_debut'])->format('H:i:s')
                                    : $row['heure_debut'],
            'date_fin'         => is_numeric($row['date_fin'])
                                    ? Date::excelToDateTimeObject($row['date_fin'])->format('Y-m-d')
                                    : $row['date_fin'],
            'heure_fin'        => is_numeric($row['heure_fin'])
                                    ? Date::excelToDateTimeObject($row['heure_fin'])->format('H:i:s')
                                    : $row['heure_fin'],
            'point'            => 1,
            'duree_initial'    => 60,
        ]);
    }

    /**
     * Taille de batch pour l'insertion
     */
    public function batchSize(): int
    {
        return 1000; // insert par 1000 lignes
    }

    /**
     * Lecture du fichier par chunks
     */
    public function chunkSize(): int
    {
        return 1000; // lecture par 1000 lignes
    }
}
