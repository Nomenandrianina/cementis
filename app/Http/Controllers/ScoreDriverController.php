<?php

namespace App\Http\Controllers;

use App\DataTables\ScoreDriverDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateScoreDriverRequest;
use App\Http\Requests\UpdateScoreDriverRequest;
use App\Repositories\ScoreDriverRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transporteur;
use App\Models\ScoreDriver;
use Illuminate\Support\Facades\DB;
use App\Models\ImportExcel;
use App\Models\Importcalendar;
use App\Models\Scoring;
use App\Exports\ScoreDriveExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ScoreDriverService;

class ScoreDriverController extends AppBaseController
{
    /** @var ScoreDriverRepository $scoreDriverRepository*/
    private $scoreDriverRepository;

    /** @var ScoreDriverService $scoreDriverService*/
    private $scoreDriverService;

    public function __construct(ScoreDriverRepository $scoreDriverRepo, ScoreDriverService $scoreDriverService)
    {
        $this->scoreDriverRepository = $scoreDriverRepo;
        $this->scoreDriverService = $scoreDriverService;
    }

    /**
     * Display a listing of the ScoreDriver.
     *
     * @param ScoreDriverDataTable $scoreDriverDataTable
     *
     * @return Response
     */
    public function index(ScoreDriverDataTable $scoreDriverDataTable)
    {
        return $scoreDriverDataTable->render('score_drivers.index');
    }

    public function score_driver(Request $request){
        $userName = Auth::user()->name;
        $transporteur_id = Transporteur::where('nom', 'like', '%' . $userName . '%')->value('id');
        $selectedPlanning = DB::table('import_calendar')->latest('id')->value('id');
        $import_calendar = Importcalendar::all();
        $scoring = ScoreDriver::when($transporteur_id, function ($query, $transporteur_id) {
            return $query->where('transporteur', $transporteur_id);
        })
        ->where('id_planning', $selectedPlanning)->orderBy('score', 'desc')->paginate(100);
        if ($request->ajax() && $request->has('page')) {
            return view('score_drivers.rows', compact('scoring', 'selectedPlanning'))->render();
        }
        return view('score_drivers.driver', compact('import_calendar', 'selectedPlanning', 'scoring'));
    }

    public function filter_score_drive_by_planning(Request $request){
        $selectedPlanning = $request->input('planning');
        
        $data = [];
        $scoring = ScoreDriver::where('id_planning', $selectedPlanning)->orderBy('score', 'desc')->paginate(100);
        return view('score_drivers.table', compact('data', 'selectedPlanning', 'scoring'));
    }

    public function export_excel_score_drive(Request $request)
    {
        $planning = $request->planning ?? null;
        $import_calendar = Importcalendar::where('id', $planning)->first();
        $file_name = "Score ".$import_calendar?->name.".xlsx";
        return Excel::download(new ScoreDriveExport($planning), $file_name);
    }

    public function detail_score_drive(Request $request)
    {
        $planning = $request->id_planning ?? null;
        $badge = $request->badge ?? null;

        $selectedPlanning = DB::table('import_calendar')->latest('id')->value('id');
        $import_calendar = Importcalendar::all();
        $alphaciment_driver = $request->query('alphaciment_driver', null);
        $scoring = Scoring::where('badge_calendar', $badge)
        ->where('id_planning', $selectedPlanning)->orderBy('point', 'desc')->paginate(100);
        
        return view('events.scoring', compact('import_calendar', 'selectedPlanning', 'scoring','alphaciment_driver'));
    }

    public function detail_score_driver_zero(Request $request){
        $selectedPlanning = $request->id_planning ?? DB::table('import_calendar')->latest('id')->value('id');
        $transporteur_id = $request->id_transporteur;

        $import_calendar = Importcalendar::all();

        // Récupérer les Scoring avec les chauffeurs et leurs mises à jour
        $scoring = ScoreDriver::where('score', 0)
        ->where('id_planning', $selectedPlanning)
        ->when($transporteur_id, fn($q) => $q->where('transporteur', $transporteur_id))
        ->paginate(100);
       

        return view('score_drivers.driver', compact('import_calendar', 'selectedPlanning', 'scoring'));
    }

    public function detail_score_driver_zero_more_than_3_planning(Request $request){
        $selectedPlanning = $request->id_planning ?? DB::table('import_calendar')->latest('id')->value('id');
        $transporteur_id = $request->id_transporteur;
        $import_calendar = Importcalendar::all();

        $badgeAvecTrajets = DB::table('import_excel')
            ->select('badge_chauffeur')
            ->where('import_calendar_id', $selectedPlanning)
            ->groupBy('badge_chauffeur')
            ->havingRaw('COUNT(id) >= 3')
            ->pluck('badge_chauffeur');

        // 2️⃣ Filtrer le scoring
        $scoring = ScoreDriver::with('driver') // charge la relation chauffeur
        ->where('score', 0)
        ->where('id_planning', $selectedPlanning)
        ->when($transporteur_id, fn($q) => $q->where('transporteur', $transporteur_id))
        ->whereIn('badge', $badgeAvecTrajets)
        ->paginate(150);
       

        return view('score_drivers.driver', compact('import_calendar', 'selectedPlanning', 'scoring'));
    }

    /**
     * Show the form for creating a new ScoreDriver.
     *
     * @return Response
     */
    public function create()
    {
        return view('score_drivers.create');
    }

    /**
     * Store a newly created ScoreDriver in storage.
     *
     * @param CreateScoreDriverRequest $request
     *
     * @return Response
     */
    public function store(CreateScoreDriverRequest $request)
    {
        $input = $request->all();

        $scoreDriver = $this->scoreDriverRepository->create($input);

        Flash::success(__('messages.saved', ['model' => __('models/scoreDrivers.singular')]));

        return redirect(route('scoreDrivers.index'));
    }

    /**
     * Display the specified ScoreDriver.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $scoreDriver = $this->scoreDriverRepository->find($id);

        if (empty($scoreDriver)) {
            Flash::error(__('messages.not_found', ['model' => __('models/scoreDrivers.singular')]));

            return redirect(route('scoreDrivers.index'));
        }

        return view('score_drivers.show')->with('scoreDriver', $scoreDriver);
    }

    /**
     * Show the form for editing the specified ScoreDriver.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $scoreDriver = $this->scoreDriverRepository->find($id);

        if (empty($scoreDriver)) {
            Flash::error(__('messages.not_found', ['model' => __('models/scoreDrivers.singular')]));

            return redirect(route('scoreDrivers.index'));
        }

        return view('score_drivers.edit')->with('scoreDriver', $scoreDriver);
    }

    /**
     * Update the specified ScoreDriver in storage.
     *
     * @param int $id
     * @param UpdateScoreDriverRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateScoreDriverRequest $request)
    {
        $scoreDriver = $this->scoreDriverRepository->find($id);

        if (empty($scoreDriver)) {
            Flash::error(__('messages.not_found', ['model' => __('models/scoreDrivers.singular')]));

            return redirect(route('scoreDrivers.index'));
        }

        $scoreDriver = $this->scoreDriverRepository->update($request->all(), $id);

        Flash::success(__('messages.updated', ['model' => __('models/scoreDrivers.singular')]));

        return redirect(route('scoreDrivers.index'));
    }

    /**
     * Remove the specified ScoreDriver from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $scoreDriver = $this->scoreDriverRepository->find($id);

        if (empty($scoreDriver)) {
            Flash::error(__('messages.not_found', ['model' => __('models/scoreDrivers.singular')]));

            return redirect(route('scoreDrivers.index'));
        }

        $this->scoreDriverRepository->delete($id);

        Flash::success(__('messages.deleted', ['model' => __('models/scoreDrivers.singular')]));

        return redirect(route('scoreDrivers.index'));
    }
}
