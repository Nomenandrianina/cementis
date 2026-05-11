<?php

namespace App\DataTables;

use App\Models\Event;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;

class EventDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable
                ->addColumn('action', 'events.datatables_actions')
                ->editColumn('latitude', function ($event) {
                    return $event->latitude . ', ' . $event->longitude;
                });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Event $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Event $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '120px', 'printable' => false, 'title' => __('crud.action')])
            ->parameters([
                'dom'       => 'Bfrtip',
                'stateSave' => true,
                'order'     => [[0, 'desc']],
                'buttons'   => [
                    'excel', 'csv', 'print',
                ],
                 'language' => __('datatables'),
            ]);
    }


    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            // 'imei' => new Column(['title' => __('models/events.fields.imei'), 'data' => 'imei']),
            'chauffeur' => new Column(['title' => __('models/events.fields.chauffeur'), 'data' => 'chauffeur']),
            'vehicule' => new Column(['title' => __('models/events.fields.vehicule'), 'data' => 'vehicule']),
            'type' => new Column(['title' => __('models/events.fields.type'), 'data' => 'type']),
            'vitesse' => new Column(['title' => __('models/events.fields.vitesse'), 'data' => 'vitesse']),
            'odometer' => new Column(['title' => __('models/events.fields.odometer'), 'data' => 'odometer']),
            'duree' => new Column([
                'title' => __('models/events.fields.duree'), 'data' => 'duree',
                'render' => 'function() {
                    return full.duree + " s";
                }',
            ]),
            'gps' => new Column([
                'title' => __('models/events.fields.gps'), 
                'data' => 'latitude', // Cette donnée contient maintenant "lat, long" grâce à editColumn
                'render' => 'function() {
                    var coords = full.latitude; 
                    return "<a href=\"#\" onclick=\"showMapModal(" + full.latitude_original + ", " + full.longitude + ", \'" + full.type + "\')\">" + coords + "</a>";
                }',
            ]),
            // 'gps' => new Column([
            //     'title' => __('models/events.fields.gps'), 'data' => 'latitude',
            //     'render' => 'function() {
            //         return "<a href=\"#\" onclick=\"showMapModal(" + full.latitude + ", " + full.longitude + ", \'" + full.type + "\')\">" + full.latitude + ", " + full.longitude + "</a>";
            //     }',
            // ]),
            'date' => new Column([
                'title' => __('models/events.fields.date'), 'data' => 'date',
                'render' => 'function() {
                    var date = new Date(full.date);
                    var date_heure = moment.utc(date).format("DD-MM-YYYY HH:mm:ss");
                    return date_heure;
                }',
            ])
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'events_datatable_' . time();
    }
}
