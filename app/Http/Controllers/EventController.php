<?php

namespace App\Http\Controllers;

use App\DataTables\EventDataTable;
use App\Exports\ScoringCardExport;
use App\Exports\ScoringExport;
use App\Http\Requests;
use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Repositories\EventRepository;
use App\Http\Controllers\AppBaseController;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\Penalite;
use App\Models\Scoring;
use App\Models\ImportExcel;
use App\Models\Importcalendar;
use Dompdf\Dompdf;
use App\Models\Chauffeur;
use GuzzleHttp\Client;
use App\Imports\SurvitesseImportClass;
use Maatwebsite\Excel\Facades\Excel;
use Response;

class EventController extends AppBaseController
{
    /** @var EventRepository $eventRepository*/
    private $eventRepository;

    public function __construct(EventRepository $eventRepo)
    {
        $this->eventRepository = $eventRepo;
    }   

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('events.import');
    }


    /**
     * Import a data infraction via excel
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function import(Request $request){
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);
        
        Excel::import(new SurvitesseImportClass, $request->file('excel_file'));

        return redirect()
            ->route('events.index')
            ->with('success', 'Importation réussie');
    }

    public function viewScoring(){
        $drivers = Chauffeur::all()->pluck('nom')->toArray();
        
        return view('events.scoring', compact('drivers'));
    }

    /**
     * Display a listing of the Event.
     *
     * @param EventDataTable $eventDataTable
     *
     * @return Response
     */
    public function index(EventDataTable $eventDataTable)
    {
        // getInfractionWithmaximumPoint();
        return $eventDataTable->render('events.index');
    }


    public function saveScoring($data){
        foreach($data as $item){
            $existingScoring = Scoring::where('id_planning', $item['id_planning'])
                    ->where('driver_id', $item['driver_id'])
                    ->where('transporteur_id', $item['transporteur_id'])
                    ->first();
    
            if ($existingScoring) {
                if (empty($existingScoring->camion)) {
                    $existingScoring->camion = getPlateNumberByRfidAndTransporteur($existingScoring->driver_id, $existingScoring->transporteur_id);
                    $existingScoring->save();
                }
            }else{
                if (empty($item['camion'])) {
                    $item['camion'] = getPlateNumberByRfidAndTransporteur($item['driver_id'], $item['transporteur_id']);
                }

                Scoring::create([
                    'id_planning' => $item['id_planning'],
                    'driver_id' => $item['driver_id'],
                    'transporteur_id' => $item['transporteur_id'],
                    'camion' => $item['camion'],
                    'comment' => $item['comment'],
                    'distance' => $item['distance'],
                    'point' => $item['point'],
                ]);
            }
        }
    }

    

    public function showMap($latitude, $longitude)
    {
        return view('events.map')->with(compact('latitude', 'longitude'));
    }


    public function TableauScoringPdf(){
        $scoring = tabScoringCard();
        $total = totalScoringCard();
    
        $pdf = new Dompdf();
        $pdf->loadHtml(view('events.table_scoring', compact('scoring', 'total'))->render());
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        return $pdf->stream('tableau_scoring.pdf');
    }


    /**
     * Show the form for creating a new Event.
     *
     * @return Response
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created Event in storage.
     *
     * @param CreateEventRequest $request
     *
     * @return Response
     */
    public function store(CreateEventRequest $request)
    {
        $input = $request->all();

        $event = $this->eventRepository->create($input);

        Alert::success(__('messages.saved', ['model' => __('models/events.singular')]));

        return redirect(route('events.index'));
    }

    /**
     * Display the specified Event.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $event = $this->eventRepository->find($id);

        if (empty($event)) {
            Alert::error(__('messages.not_found', ['model' => __('models/events.singular')]));

            return redirect(route('events.index'));
        }

        return view('events.show')->with('event', $event);
    }

    /**
     * Show the form for editing the specified Event.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $event = $this->eventRepository->find($id);

        if (empty($event)) {
            Alert::error(__('messages.not_found', ['model' => __('models/events.singular')]));

            return redirect(route('events.index'));
        }

        return view('events.edit')->with('event', $event);
    }

    /**
     * Update the specified Event in storage.
     *
     * @param int $id
     * @param UpdateEventRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateEventRequest $request)
    {
        $event = $this->eventRepository->find($id);

        if (empty($event)) {
            Alert::error(__('messages.not_found', ['model' => __('models/events.singular')]));

            return redirect(route('events.index'));
        }

        $event = $this->eventRepository->update($request->all(), $id);

        Alert::success(__('messages.updated', ['model' => __('models/events.singular')]));

        return redirect(route('events.index'));
    }

    /**
     * Remove the specified Event from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $event = $this->eventRepository->find($id);

        if (empty($event)) {
            Alert::error(__('messages.not_found', ['model' => __('models/events.singular')]));

            return redirect(route('events.index'));
        }

        $this->eventRepository->delete($id);

        Alert::success(__('messages.deleted', ['model' => __('models/events.singular')]));

        return redirect(route('events.index'));
    }
}
