@extends('layouts.app')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA1f_TK4EnA9ZIQIv6_o5piA48iW8tuHoQ"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                   <h1>@lang('models/events.plural')</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('events.upload.file') }}">
                         Téléverser un fichier
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')


        <div class="card">
            <div class="card-body p-0">
                <!-- Modal pour afficher la carte -->
                <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="mapModalLabel">Carte</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </div>
                        <div class="modal-body">
                        <div id="map" style="height: 400px;"></div>
                        </div>
                    </div>
                    </div>
                </div>
                @include('events.table')
            
  
                <div class="card-footer clearfix float-right">
                    <div class="float-right">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        let map;

        async function initMap(latitude, longitude, type) {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: parseFloat(latitude), lng: parseFloat(longitude)},
                zoom: 15
            });

            var marker = new google.maps.Marker({
                position: {lat: parseFloat(latitude), lng: parseFloat(longitude)},
                map: map,
                title: type
            });
        }


        function showMapModal(latitude, longitude, type) {
            initMap(latitude, longitude, type);
            $('#mapModal').modal('show');
        }
    </script>
    
@endsection


    


