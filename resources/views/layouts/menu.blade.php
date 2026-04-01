@php
    $urlAdmin = config('fast.admin_prefix');
@endphp

@can('dashboard')
    @php
        $isDashboardActive = Request::is($urlAdmin);
    @endphp
    <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ $isDashboardActive ? 'active' : '' }}" onclick="submitForm()">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>@lang('menu.dashboard')</p>
        </a>
    </li>
@endcan

{{-- @can('generator_builder.index')
    @php
        $isUserActive = Request::is($urlAdmin . '*generator_builder*');
    @endphp
    <li class="nav-item">
        <a href="{{ route('generator_builder.index') }}" class="nav-link {{ $isUserActive ? 'active' : '' }}">
            <i class="nav-icon fas fa-coins"></i>
            <p>@lang('menu.generator_builder.title')</p>
        </a>
    </li>
@endcan --}}


@can('attendances.index')
    @php
        $isUserActive = Request::is($urlAdmin . '*attendances*');
    @endphp

    {{-- <li class="nav-item">
    <a href="{{ route('attendances.index') }}" class="nav-link {{ $isUserActive ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-alt"></i>

        <p>@lang('menu.attendances.title')</p>
    </a>
</li> --}}
@endcan

@canany(['users.index', 'roles.index', 'permissions.index'])
    @php
        $isUserActive = Request::is($urlAdmin . '*users*');
        $isRoleActive = Request::is($urlAdmin . '*roles*');
        $isPermissionActive = Request::is($urlAdmin . '*permissions*');
    @endphp

    @if (Auth::user()->hasRole('supper-admin'))
        <li class="nav-item {{ $isUserActive || $isRoleActive || $isPermissionActive ? 'menu-open' : '' }} ">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-shield-virus"></i>
                <p>
                    @lang('menu.user.title')
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @can('users.index')
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ $isUserActive ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                @lang('menu.user.users')
                            </p>
                        </a>
                    </li>
                @endcan
                @can('roles.index')
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}" class="nav-link {{ $isRoleActive ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>
                                @lang('menu.user.roles')
                            </p>
                        </a>
                    </li>
                @endcan
                @can('permissions.index')
                    <li class="nav-item ">
                        <a href="{{ route('permissions.index') }}" class="nav-link {{ $isPermissionActive ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            <p>
                                @lang('menu.user.permissions')
                            </p>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif
@endcan


<li class="nav-item">
    <a href="{{ route('driver.score') }}" class="nav-link {{ Request::is('admin/new/scoring*') ? 'active' : '' }}"
        onclick="submitForm()">
        <i class="nav-icon fas fa-bullseye"></i>
        <p>@lang('models/events.fields.scoring')</p>
    </a>
</li>




{{-- @canany(['importcalendars.index', 'importExcels.index'])
    @php
        $isImportCalendarsActive = Request::is('admin/importcalendars*');
        $isImportExcelsActive = Request::is('admin/importExcels*');
    @endphp
    <li class="nav-item has-treeview {{ $isImportCalendarsActive || $isImportExcelsActive  ? 'menu-open' : '' }}">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-calendar"></i>
            <p>
                @lang('models/importExcels.fields.import_calendar')
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>


        <ul class="nav nav-treeview" style="padding-left:8px">
            @can('importcalendars.index')
                <li class="nav-item">
                    <a href="{{ route('importcalendars.index') }}"
                        class="nav-link {{ Request::is('admin/importcalendars*') ? 'active' : '' }}" onclick="submitForm()">
                        <i class="nav-icon fas fa-list"></i>
                        <p>@lang('models/importExcels.fields.import_list')</p>
                    </a>
                </li>
            @endcan
            @can('importExcels.index')
                <li class="nav-item">
                    <a href="{{ route('importExcels.index') }}"
                        class="nav-link {{ Request::is('admin/importExcels*') ? 'active' : '' }}" onclick="submitForm()">
                        <i class="nav-icon fas fa-file"></i>
                        <p>@lang('models/importExcels.fields.import_detail')</p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany --}}





@canany(['chauffeurs.index', 'penalites.index', 'transporteurs.index', 'vehicules.index', 'chauffeurUpdateTypes.index'])
@php
    $isChauffeurActive = Request::is('admin/chauffeurs*');
    $isPenalitesActive = Request::is('admin/penalites*');
    $isTransporteurActive = Request::is('admin/transporteurs*');
    $isVehiculeActive = Request::is('admin/vehicules*');
    $isChauffeurUpdateActive = Request::is('admin/chauffeurUpdateTypes*');
@endphp
    <li class="nav-item has-treeview {{ $isChauffeurActive || $isPenalitesActive || $isTransporteurActive || $isVehiculeActive || $isChauffeurUpdateActive ? 'menu-open' : '' }}">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-database"></i>
            <p>
                @lang('menu.database.title')
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview" style="padding-left:8px">
            @can('chauffeurs.index')
                <li class="nav-item">
                    <a href="{{ route('chauffeurs.index') }}"
                        class="nav-link {{ Request::is('admin/chauffeurs*') ? 'active' : '' }}" onclick="submitForm()">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>@lang('models/chauffeurs.plural')</p>
                    </a>
                </li>
            @endcan
           
            @can('transporteurs.index')    
                <li class="nav-item">
                    <a href="{{ route('transporteurs.index') }}"
                        class="nav-link {{ Request::is('admin/transporteurs*') ? 'active' : '' }}" onclick="submitForm()">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>@lang('models/transporteurs.plural')</p>
                    </a>
                </li>
            @endcan
            @can('vehicules.index')   
                <li class="nav-item">
                    <a href="{{ route('vehicules.index') }}" class="nav-link {{ Request::is('admin/vehicules*') ? 'active' : '' }}"
                        onclick="submitForm()">
                        <i class="nav-icon fas fa-car"></i>
                        <p>@lang('models/vehicules.plural')</p>
                    </a>
                </li>
            @endcan
            @can('movements.index')  
                <li class="nav-item">
                    <a href="{{ route('movements.index') }}" class="nav-link {{ Request::is('admin/movements*') ? 'active' : '' }}">
                        <i class="nav-icon fa fa-route"></i>
                        <p>@lang('models/movements.plural')</p>
                    </a>
                </li>
            @endcan
            @can('infractions.index')
                <li class="nav-item">
                    <a href="{{ route('infractions.index') }}" class="nav-link {{ Request::is('admin/infractions*') ? 'active' : '' }}"
                        onclick="submitForm()">
                        <i class="nav-icon fas fa-virus"></i>
                        <p>@lang('models/infractions.plural')</p>
                    </a>
                </li>
            @endcan

            @can('events.index')
                <li class="nav-item">
                    <a href="{{ route('events.index') }}" class="nav-link {{ Request::is('admin/events*') ? 'active' : '' }}"
                        onclick="submitForm()">
                        <i class="nav-icon fas fa-calendar"></i>
                        <p>@lang('models/events.plural')</p>
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endcanany

@can('process.index')    
    <li class="nav-item">
        <a href="{{ route('process.index') }}" class="nav-link {{ Request::is('admin/process*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-microchip"></i>
            <p>@lang('models/process.plural')</p>
        </a>
    </li>
@endcan


@can('importNameInstallations.index')   
    <li class="nav-item">
        <a href="{{ route('importNameInstallations.index') }}"
            class="nav-link {{ Request::is('admin/importNameInstallations*') ? 'active' : '' }}">
            <i class="nav-icon fa fa-upload"></i>
            <p>Import chauffeurs</p>
        </a>
    </li>
@endcan





@can('chauffeurUpdateStorie.validation_list')    
    <li class="nav-item">
        <a href="{{ route('chauffeurUpdateStorie.validation_list') }}"
        class="nav-link {{ Request::is('admin/chauffeurUpdateStorie*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-list"></i>
            <p>@lang('models/chauffeurUpdateStories.plural')</p>
        </a>
    </li>
@endcan


<li class="nav-item">
    <a href="{{ route('rotations.index') }}" class="nav-link {{ request()->routeIs('rotations.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        Rotations
    </a>
</li>

<li class="nav-item">
   <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Rapports
    </a>
</li>

<li class="nav-item has-treeview {{ $isChauffeurActive || $isPenalitesActive || $isTransporteurActive || $isVehiculeActive || $isChauffeurUpdateActive ? 'menu-open' : '' }}">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-database"></i>
        <p>
            Paramétrage
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview" style="padding-left:8px">
        @can('chauffeurs.index')
            <li class="nav-item">
                <a href="{{ route('circuits.index') }}" class="nav-link {{ request()->routeIs('circuits.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Circuits
                </a>
            </li>

        @endcan
        
        @can('transporteurs.index')    
            <li class="nav-item">
                <a href="{{ route('checkpoints.index') }}" class="nav-link {{ request()->routeIs('checkpoints.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Checkpoints
                </a>
            </li>
        @endcan
        @can('vehicules.index')   
            <li class="nav-item">
                <a href="{{ route('zones.index') }}" class="nav-link {{ request()->routeIs('zones.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                    </svg>
                    Zones
                </a>
            </li>
        @endcan

        <li class="nav-item">
            <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2 2-2h6z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 8h4l3 4v3h-2m-5-7v7"/>
                </svg>
                Camions
            </a>
        </li>

    </ul>
</li>


{{-- @can('chauffeurUpdateStorie.validation_list')    
    <li class="nav-item">
        <a href="{{ route('incident.index') }}"
        class="nav-link {{ Request::is('admin/chauffeurUpdateStorie*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-list"></i>
            <p>Rapport d'incident</p>
        </a>
    </li>
@endcan --}}

