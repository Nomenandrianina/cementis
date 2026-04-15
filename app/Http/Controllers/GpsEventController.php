<?php

namespace App\Http\Controllers;

use App\Models\Rvehicule;
use App\Services\GpsApiService;
use App\Services\GpsEventMapper;
use Illuminate\Http\Request;

class GpsEventController extends Controller
{
    public function __construct(
        private readonly GpsApiService  $gpsApi,
        private readonly GpsEventMapper $mapper
    ) {}

    public function index()
    {
        $vehicles = Rvehicule::orderBy('name')->get();
        return view('rotations.event', compact('vehicles'));
    }

    public function fetch(Request $request)
    {
        $data = $request->validate([
            'imei'      => 'required|string',
            'date_from' => 'required|date_format:Y-m-d',
            'date_to'   => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'use_test'  => 'nullable|boolean',
            'test_mode' => 'nullable|string|in:complete,incomplete,cancelled,real_sample,antonio',
        ]);

        $vehicle = Rvehicule::where('imei', $data['imei'])->first();

        if ($request->boolean('use_test')) {
            $rawEvents = $this->getTestEvents($data['test_mode'] ?? 'complete');
            $source    = 'test';
        } else {
            $from      = str_replace('-', '', $data['date_from']) . '000000';
            $to        = str_replace('-', '', $data['date_to'])   . '235959';
            $rawEvents = $this->gpsApi->getEvents($data['imei'], $from, $to);
            $source    = 'api';
        }

        if (empty($rawEvents)) {
            return response()->json([
                'success'  => false,
                'message'  => 'Aucun événement trouvé pour cette période.',
                'raw_count'=> 0,
                'events'   => [],
                'source'   => $source,
            ]);
        }

        // Filtrer uniquement zone_in, zone_out, marker_in
        $relevantTypes = ['zone_in', 'zone_out', 'marker_in'];
        $filtered = array_values(array_filter(
            $rawEvents,
            fn($e) => in_array(strtolower($e[0] ?? ''), $relevantTypes)
        ));

        // Normaliser
        $normalized = $this->mapper->normalize($filtered);

        $events = array_map(fn($e) => [
            'raw_type'        => $e['raw_type'],
            'normalized_type' => $e['normalized_type'],
            'reference_name'  => $e['reference_name'],
            'zone_id'         => $e['zone_id'],
            'checkpoint_id'   => $e['checkpoint_id'],
            'dt'              => $e['dt'],
            'lat'             => $e['lat'],
            'lng'             => $e['lng'],
            'in_db'           => $e['zone_id'] !== null || $e['checkpoint_id'] !== null,
        ], $normalized);

        return response()->json([
            'success'          => true,
            'source'           => $source,
            'raw_count'        => count($rawEvents),
            'filtered_count'   => count($filtered),
            'normalized_count' => count($events),
            'vehicle'          => $vehicle
                ? ['name' => $vehicle->name, 'plate_number' => $vehicle->plate_number, 'imei' => $vehicle->imei]
                : ['name' => $data['imei'],  'plate_number' => null,                   'imei' => $data['imei']],
            'period'           => ['from' => $data['date_from'], 'to' => $data['date_to']],
            'events'           => $events,
        ]);
    }

    private function getTestEvents(string $mode): array
    {
        return match($mode) {
            'complete'    => \App\Services\TestRawEvents::completeRotation(),
            'incomplete'  => \App\Services\TestRawEvents::incompleteRotation(),
            'cancelled'   => \App\Services\TestRawEvents::cancelledRotation(),
            'real_sample' => \App\Services\TestRawEvents::realApiSample(),
            'antonio'     => \App\Services\TestRawEvents::completeRotationAntonio(),
            default       => \App\Services\TestRawEvents::completeRotation(),
        };
    }
}
