@extends('layouts.full')
@push('page_body_class')
hold-transition login-page
@endpush

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ route('home') }}"><img src="{{url('images/alpha_ciment.jpg')}}" alt="" style="width: 70%;
            border-radius: 20px;"></a>
    </div>

    <!-- /.login-logo -->
    <div id="loader"  class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    <div id="overlay"></div>
    <!-- /.login-box-body -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Veuillez vous connecter à votre compte</p>

            <form method="post" action="{{ url('/login') }}">
                @csrf

                <div class="input-group mb-3">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="@lang('auth.login.field.email')" class="form-control @error('email') is-invalid @enderror">
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                    @error('email')
                    <span class="error invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" placeholder="@lang('auth.login.field.password')" class="form-control @error('password') is-invalid @enderror">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                    <span class="error invalid-feedback">{{ $message }}</span>
                    @enderror

                </div>

                <div class="row">
                    {{-- <div class="col">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">@lang('auth.login.field.remember')</label>
                        </div>
                    </div> --}}

                    <div class="col">
                        <button type="submit" class="btn btn-primary btn-block" onclick="submitForm()">Connexion</button>
                    </div>

                </div>
            </form>

            {{-- <p class="mb-1">
                <a href="{{ route('password.request') }}">@lang('auth.login.button.reset-password')</a>
            </p>
            <p class="mb-1">
                <a href="{{ route('register') }}" class="text-center">@lang('auth.login.button.register')</a>
            </p> --}}


        </div>
        <!-- /.login-card-body -->
    </div>
    {{-- @include('layouts.lang') --}}
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>

<script type="text/javascript">
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

<style>
     #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(128, 128, 128, 0.7); /* Couleur semi-transparente gris */
        z-index: 9998; /* Assure que l'overlay est au-dessus de tout autre contenu */
    }
  
    .lds-roller {
        display: none; /* Pour masquer le loader initialement */
        position: fixed;
        width: 80px;
        height: 80px;
        top: 50%;
        left: 50%;
        margin-top: -40px; /* La moitié de la hauteur du loader */
        margin-left: -40px; /* La moitié de la largeur du loader */
        z-index: 9999;
        color: #ffffff; /* Couleur du loader */
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
        background: currentColor; /* Utilise la couleur définie dans .lds-roller */
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
</style>

@endsection
