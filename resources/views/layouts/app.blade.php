<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Remplacez le chemin avec le vôtre -->
    <link rel="icon" type="image/png" href="{{ asset('images/alpha_ciment.jpg') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="{{ mix('js/app.js') }}"></script>

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/css/adminlte.min.css" integrity="sha512-rVZC4rf0Piwtw/LsgwXxKXzWq3L0P6atiQKBNuXYRbg2FoRbSTIY0k2DxuJcs7dk4e/ShtMzglHKBOJxW8EQyQ==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- iCheck -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css" integrity="sha512-8vq2g5nHE062j3xor4XxPeZiPjmRDh6wlufQlfC6pdQ/9urJkU07NM0tEREeymP++NczacJ/Q59ul+/K2eYvcg==" crossorigin="anonymous" />
    <!-- select2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" />
    <!-- flag-icon-css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" integrity="sha512-Cv93isQdFwaKBV+Z4X8kaVBYWHST58Xb/jVOcV9aRsGSArZsgAnFIhMpDoMDcFNoUtday1hdjn0nGp3+KZyyFw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- tempusdominus-bootstrap-4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" integrity="sha512-3JRrEUwaCkFUBLK1N8HehwQgu8e23jTH4np5NHOmQOobuC4ROQxFwFgBLTnhcnQRMs84muMh0PnnwXlPq5MGjg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @stack('third_party_stylesheets')
    @stack('page_css')

</head>

<body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">
        <!-- Main Header -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">

            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative d-flex align-items-center justify-content-center" 
                    href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-expanded="false" 
                    style="width: 40px; height: 40px;">
                        
                        <i class="fas fa-bell" style="font-size: 1.3rem; color: #64748b;"></i>

                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span id="notif-badge" 
                                class="position-absolute badge rounded-pill bg-danger shadow-sm" 
                                style="top: 2px; right: 2px; font-size: 0.65rem; padding: 0.35em 0.5em; border: 2px solid #fff; z-index: 10;">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>

                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown" style="width: 646px; max-height: 400px; overflow-y: auto;">
                        <div class="dropdown-header">Notifications</div>
                        @forelse (Auth::user()->notifications as $notification)

                            <div class="d-flex align-items-start p-3" style="border-radius: 5px; background-color: #f8f9fa; margin-bottom: 5px;">
                                <!-- Icône de notification agrandie -->
                                <div class="notification-icon" style="width: 40px; height: 40px; background-color: #e0e0e0; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 10px;">
                                    <i class="fa fa-edit text-primary" style="font-size: 20px;"></i>
                                </div>
                                <!-- Détails de la notification -->
                                <a class="w-100" href="{{ $notification->data['url'] }}">
                                    <div class="mb-1 font-weight-bold" style="color: #333;"  >
                                        {{ $notification->data['message']  }}
                                    </div>
                                    <small style="display: block;">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </a>
                            </div>
                        @empty
                            <span class="dropdown-item text-muted">Aucune notification</span>
                        @endforelse
                        <li><hr class="dropdown-divider"></li>
                    </ul>
                </li>

                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" id="userDropdown">
                        {{-- <img src="{{url('images/avatars.png')}}" class="user-image img-circle elevation-2" alt="User Image"> --}}
                        <div class="user-avatar shadow-sm d-flex align-items-center justify-content-center" 
                            style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); 
                                    width: 32px; height: 32px; border-radius: 8px; 
                                    color: white; font-size: 0.9rem; font-weight: bold; 
                                    transition: transform 0.2s ease;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="d-none d-md-inline ml-2 font-weight-600" style="color: #4b5563;">
                            {{ Auth::user()->name }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right" aria-labelledby="userDropdown">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="{{url('images/alpha_ciment.jpg')}}" class="img-circle elevation-2" alt="User Image">
                            <p>
                                {{ Auth::user()->name }}
                                <small>Membre depuis {{ Auth::user()->created_at->format('M. Y') }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="{{route('users.profile')}}" class="btn btn-default btn-flat">Profile</a>
                            <a href="#" class="btn btn-default btn-flat float-right" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Déconnexion
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Left side column. contains the logo and sidebar -->
        @include('sweetalert::alert')
        @include('layouts.sidebar')



        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content pt-2">

                <div id="loader"  class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                <div id="overlay"></div>
                @if(Route::is([ 'rotations.show', 'reports.*', 'circuits.edit', 'circuits.objectives.*', 'checkpoints.*', 'zones.*', 'vehicles.*']))
                    <div class="topbar">
                        <div class="topbar-title">@yield('page-title')</div>
                        <div class="topbar-actions">@yield('topbar-actions')</div>
                    </div>
                @endif
                @yield('content')
            </section>
        </div>


        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 2.0.0
            </div>
            <strong>Droits d'auteur &copy; 2023 - {{ date('Y') }} <a href="https://www.m-tec.mg/">M-Tec</a>.</strong> Tous droits réservés.
        </footer>
    </div>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/js/adminlte.min.js" integrity="sha512-++c7zGcm18AhH83pOIETVReg0dr1Yn8XTRw+0bWSIWAVCAwz1s2PwnSj4z/OOyKlwSXc4RLg3nnjR22q0dhEyA==" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js" integrity="sha512-rmZcZsyhe0/MAjquhTgiUcb4d9knaFc7b5xAfju483gbEXTkeJRUMIPk6s3ySZMYUHEcjKbjLjyddGWMrNEvZg==" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-k6/Bkb8Fxf/c1Tkyl39yJwcOZ1P4cRrJu77p83zJjN2Z55prbFHxPs9vN7q3l3+tSMGPDdoH51AEU8Vgo1cgAA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js" integrity="sha512-J+763o/bd3r9iW+gFEqTaeyi+uAphmzkE/zU8FxY6iAvD3nQKXa+ZAWkBI9QS9QkYEKddQoiy0I5GDxKf/ORBA==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> --}}
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <script type="text/javascript">
        console.log(window.Echo);
        window.Echo.channel('job-completed')
        .listen('.job.completed', (event) => {
            console.log('Événement reçu :', event);
            if (event.status === 'completed') {
                Swal.fire({
                    icon: 'success',
                    title: event.process.name,
                    text: `L'exécution de l'étape ${event.process.name} est terminée avec succès !`,
                    confirmButtonText: 'Ok'
                }).then(() => {
                    window.location.reload(); // Recharge aussi en cas d'erreur
                });
            }
            if (event.status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: event.process.name,
                    text: `Erreur lors de l'exécution de l'étape ${event.process.name}.`,
                    confirmButtonText: 'Ok'
                    // timer: 3000,
                    // showConfirmButton: false
                }).then(() => {
                    window.location.reload(); // Recharge aussi en cas d'erreur
                });
            }
        });

        window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('Pusher connecté');
        });

        window.Echo.connector.pusher.connection.bind('error', (error) => {
            console.log('Erreur Pusher:', error);
        });
        $(document).ready(function() {
            // Masquer le loader et l'overlay lorsque la page est chargée
            $('#overlay').hide();
            $('#loader').hide();
        });

        function submitForm() {
            // Afficher le loader
            $('#overlay').show();
            $('#loader').show();
            return true; // Permettre la soumission du formulaire
        }
    </script>

    <script type="text/javascript">


        $(function() {
            bsCustomFileInput.init();
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
        setInterval(function() {
            $.get("{{url('/checkOnline')}}", function($rs) {
                if ($('#user_online').length)
                    $('#user_online').html($rs);
            })
        }, 10000);


        // Fonction pour modifier ou ajouter le transporteur_id selectionné dans la table transporteur
        function update_transporteurid(id){

            var selectedValues = [];
            var checkboxes = document.querySelectorAll('.select-checkbox:checked');
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });

            if(selectedValues.length === 0){

                Swal.fire({
                    title: 'Message',
                    text:  'Veuillez selectionner un ou plusieurs chauffeurs!',
                    icon: 'info',
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 2000
                });

            }else{

                submitForm();

                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                $.ajax({
                    type: 'POST',
                    url: '/admin/chauffeur/updatetransporteur',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },

                    data: {
                        transporteur_id : id,
                        chauffeur: selectedValues
                    },

                    success: function (response) {
                        window.location.reload();

                        Swal.fire({
                            title: 'Succès!',
                            text:  'Validation efféctuée',
                            icon: 'success',
                            showCancelButton: false,
                            showConfirmButton: false,
                            timer: 2000
                        });

                        $('#overlay').hide();
                        $('#load_test').hide();

                    },
                    error: function (xhr, status, error) {
                        alert('Erreur lors de la mise à jour de l\'état du client : ' + error);
                        // Afficher un message d'erreur ou effectuer d'autres actions si nécessaire
                    }
                });

            }

        }


        var filterElement = document.getElementById('filter');

        if (filterElement) {
            filterElement.addEventListener('change', function () {
                var transporteurId = this.value;
                console.log("value",transporteurId);

                // Faites appel à la fonction de filtrage AJAX
                filterChauffeurs(transporteurId);
            });
        }


        function filterChauffeurs(transporteurId) {
            fetch('/admin/chauffeur/filtre', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ transporteur_id: transporteurId })
            })
            .then(response => response.json())
            .then(data => {
                // Mettez à jour la table avec les données filtrées
                updateTable(data);
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }


        function updateTable(data) {
            // Mettez à jour le contenu de la table avec les données filtrées
            var tbody = document.querySelector('.table tbody');
            tbody.innerHTML = '';

            data.forEach(function (chauffeur) {
                var row = `<tr>
                    <td><input type="checkbox" class="select-checkbox" name="selected_chauffeurs[]" value="${chauffeur.id}"></td>
                    <td>${chauffeur.rfid}</td>
                    <td>${chauffeur.nom}</td>
                    <td>${chauffeur.transporteur ? chauffeur.transporteur.nom : ''}</td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        // function updateNotifications() {
        //     $.get("{{ route('notifications.fetch') }}", function (data) {
        //         $('#notificationsDropdown .badge').text(data.count);

        //         let dropdownMenu = $('#notificationsDropdown').next('.dropdown-menu');
        //         dropdownMenu.empty();

        //         if (data.notifications.length > 0) {
        //             data.notifications.forEach(notification => {
        //                 dropdownMenu.append(`


        //                     <div class="d-flex align-items-start p-3" style="border-radius: 5px; background-color: #f8f9fa; margin-bottom: 5px;">
        //                         <!-- Icône de notification agrandie -->
        //                         <div class="notification-icon" style="width: 40px; height: 40px; background-color: #e0e0e0; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 10px;">
        //                             <i class="fa fa-edit text-primary" style="font-size: 20px;"></i>
        //                         </div>
        //                         <!-- Détails de la notification -->

        //                         <a class="w-100" href=" ${notification.url}">
        //                             <div class="mb-1 font-weight-bold" style="color: #333;"  >
        //                                 ${notification.message}
        //                             </div>
        //                         </a>
        //                     </div>
        //                 `);
        //             });

        //             dropdownMenu.append('<li><hr class="dropdown-divider"></li>');
        //         } else {
        //             dropdownMenu.append('<li><a class="dropdown-item text-muted">Aucune notification</a></li>');
        //         }
        //     });
        // }

        // document.addEventListener("DOMContentLoaded", function() {
        //     document.getElementById("notificationsDropdown").addEventListener("click", function() {
        //         // Envoyer la requête AJAX pour marquer les notifications comme lues

        //         let badge = document.getElementById("notif-badge");
        //         if (badge && badge.style.display !== "none") {
        //             console.log('tafiditra');
        //             // Envoyer la requête AJAX pour marquer comme lues
        //             fetch("{{ route('notifications.markAsRead') }}", {
        //                 method: "POST",
        //                 headers: {
        //                     "X-CSRF-TOKEN": "{{ csrf_token() }}",
        //                     "Content-Type": "application/json",
        //                 },
        //             })
        //             .then(response => response.json())
        //             .then(data => {
        //                 if (data.success) {
        //                     // Masquer seulement le badge, sans toucher la liste des notifications
        //                     badge.style.display = "none";
        //                 }
        //             })
        //             .catch(error => console.error("Erreur lors du marquage des notifications :", error));
        //         }

        //     });
        // });

        function updateNotifications() {
            $.get("{{ route('notifications.fetch') }}", function (data) {
                let badge = $('#notif-badge');
                
                // Mise à jour du badge
                if (data.count > 0) {
                    badge.text(data.count).show();
                } else {
                    badge.hide();
                }

                // On cible uniquement la liste des notifications
                let dropdownMenu = $('#notificationsDropdown').next('.dropdown-menu');
                
                // On garde le header s'il existe, on vide le reste
                dropdownMenu.find('.notification-item').remove(); 
                dropdownMenu.find('.no-notif').remove();

                if (data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        dropdownMenu.prepend(`
                            <div class="notification-item d-flex align-items-start p-3 border-bottom" style="background-color: #f8f9fa;">
                                <div class="notification-icon mr-3" style="width: 35px; height: 35px; background-color: #e3f2fd; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                    <i class="fa fa-bell text-primary" style="font-size: 16px;"></i>
                                </div>
                                <a class="w-100 text-decoration-none" href="${notification.url}">
                                    <div class="mb-1 font-weight-bold" style="color: #333; font-size: 0.9rem;">
                                        ${notification.message}
                                    </div>
                                </a>
                            </div>
                        `);
                    });
                } else {
                    dropdownMenu.append('<div class="no-notif p-3 text-center text-muted">Aucune notification</div>');
                }
            });
        }

        

        $(document).ready(function() {
            // S'exécute à chaque fois que le menu s'ouvre
            $('#notificationsDropdown').parent().on('show.bs.dropdown', function () {
                let badge = document.getElementById("notif-badge");

                // Si le badge est visible, on marque comme lu
                if (badge && badge.style.display !== "none") {
                    fetch("{{ route('notifications.markAsRead') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json",
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $(badge).fadeOut(); // Effet fluide pour faire disparaître le badge
                        }
                    })
                    .catch(error => console.error("Erreur :", error));
                }
            });
        });


    </script>



<style>
    .topbar {
            background: #fff;
            border-radius: 8px;
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.01em;
            flex: 1;
        }

        .topbar-actions { display: flex; gap: 8px; align-items: center; }


        .dataTables_wrapper {
            margin: 20px;
        },


        .transporteur-icon {
            color: rgb(61, 134, 203);
        }

        .top-icon {
            color: #eded35;
        }

        .worst-icon {
            color: red;
        }

        .required:after {
            content: '(*)';
            color: red;
            padding-left: 5px;
        },

        .nav-child{
            padding-left: 8px;
        },

        .card-list{
            padding:8px
        }

        .number-circle {
            width: 30px;
            height: 30px;
            background-color: #28a745;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .number-circle-worst {
            width: 30px;
            height: 30px;
            background-color: #dc3545;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .rounded-card{
            border-radius: 36px !important;
        }

        .title-scoring{
            padding-left: 12px;
            padding-bottom: 12px;
        }

     #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(128, 128, 128, 0.7);
        z-index: 9998;
    }

    .lds-roller {
        display: none;
        position: fixed;
        width: 80px;
        height: 80px;
        top: 50%;
        left: 50%;
        margin-top: -40px;
        margin-left: -40px; 
        z-index: 9999;
        color: #ffffff; 
    }

    .lds-roller div {
        animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        transform-origin: 40px 40px;
    }

    .lds-roller div:after {
        content: " ";
        display: block;
        position: absolute;
        width: 7.2px;
        height: 7.2px;
        border-radius: 50%;
        background: currentColor; 
        margin: -3.6px 0 0 -3.6px;
    }

    .lds-roller div:nth-child(1) {
        animation-delay: -0.036s;
    }
    .lds-roller div:nth-child(1):after {
        top: 62.62742px;
        left: 62.62742px;
    }
    .lds-roller div:nth-child(2) {
        animation-delay: -0.072s;
    }
    .lds-roller div:nth-child(2):after {
        top: 67.71281px;
        left: 56px;
    }
    .lds-roller div:nth-child(3) {
        animation-delay: -0.108s;
    }
    .lds-roller div:nth-child(3):after {
        top: 70.90963px;
        left: 48.28221px;
    }
    .lds-roller div:nth-child(4) {
        animation-delay: -0.144s;
    }
    .lds-roller div:nth-child(4):after {
        top: 72px;
        left: 40px;
    }
    .lds-roller div:nth-child(5) {
        animation-delay: -0.18s;
    }
    .lds-roller div:nth-child(5):after {
        top: 70.90963px;
        left: 31.71779px;
    }
    .lds-roller div:nth-child(6) {
        animation-delay: -0.216s;
    }
    .lds-roller div:nth-child(6):after {
        top: 67.71281px;
        left: 24px;
    }
    .lds-roller div:nth-child(7) {
        animation-delay: -0.252s;
    }
    .lds-roller div:nth-child(7):after {
        top: 62.62742px;
        left: 17.37258px;
    }
    .lds-roller div:nth-child(8) {
        animation-delay: -0.288s;
    }
    .lds-roller div:nth-child(8):after {
        top: 56px;
        left: 12.28719px;
    }

    @keyframes lds-roller {
        0% {
        transform: rotate(0deg);
        }
        100% {
        transform: rotate(360deg);
        }
    }

    .dropdown-menu {
        max-height: 400px; 
        overflow-y: auto;  
        width: 646px;      
    }
</style>



    @stack('third_party_scripts')

    @stack('page_scripts')
</body>

</html>
