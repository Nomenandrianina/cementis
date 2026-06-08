@push('third_party_stylesheets')
    @include('layouts.datatables_css')
    <link rel="stylesheet" href="{{ asset('css/user-table.css') }}">
@endpush

    <div class="alpha-table-card">
        <div class="table-responsive p-3">
            {{-- {!! $dataTable->table(['width' => '100%', 'class' => 'table table-striped table-bordered']) !!} --}}
            {!! $dataTable->table(['width' => '100%', 'class' => 'table alpha-modern-table']) !!}
        </div>
    </div>

@push('third_party_scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function () {
            $('#filter-planning').on('change', function () {
                $('#chauffeur-table').DataTable().ajax.reload();
            });
        });
    </script>
@endpush