<?php

namespace App\Imports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithValidation;

class SurvitesseImportClass implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    public function rules(): array
    {
        return [
            'imei'       => 'required',
            'type'      => 'required',
            'vehicule'      => 'required',
            'date' => 'required|date',
        ];
    }
    /**
    * @param Collection $rows
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Event([
            'imei'             => $row['imei'],
            'chauffeur'        => $row['chauffeur'],
            'vehicule'         => $row['vehicule'],
            'type'            => $row['type'],
            'vitesse'         => $row['vitesse'],
            'duree'    => $row['duree'],
            'date'       => is_numeric($row['date'])
                                    ? Date::excelToDateTimeObject($row['date'])->format('Y-m-d H-i-s')
                                    : $row['date'],
        ]);
    }

    /**
     * Taille de batch pour l'insertion
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Lecture du fichier par chunks
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
