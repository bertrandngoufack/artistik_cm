<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="icon" type="image/svg+xml" href="{{ asset('img/boutik/logo-mark.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') · {{ config('app.name', 'Boutik') }}</title>

    @include('layouts.partials.css')
    @include('layouts.partials.extracss_auth')

    @if(config("constants.enable_recaptcha"))
        <script src="https://www.google.com/recaptcha/api.js"></script>
    @endif
</head>

<body class="pace-done">
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span"
               data-status="{{ session('status.success') }}"
               data-msg="{{ session('status.msg') }}">
    @endif

    {{-- Barre supérieure : logo + actions --}}
    <header class="tw-absolute tw-top-4 md:tw-top-6 tw-left-0 tw-right-0 tw-px-5 md:tw-px-10 tw-z-10
                   tw-flex tw-items-center tw-justify-between">
        <a href="{{ url('/') }}" class="boutik-brand-logo">
            <img src="{{ asset('img/boutik/logo-mark.svg') }}" alt="Boutik Cameroun" width="48" height="48">
            <span class="boutik-brand-text">
                <span class="name">Boutik</span><br>
                <span class="tag">Gestion Commerciale Cameroun</span>
            </span>
        </a>

        <div class="tw-flex tw-items-center tw-gap-3 md:tw-gap-5">
            @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                @if (config('constants.allow_registration'))
                    <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{ '?lang=' . request()->lang }}@endif"
                       class="boutik-topbar-link">
                        {{ __('business.register') }}
                    </a>
                @endif
            @endif
            @if ($request->segment(1) != 'login')
                <a href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'login']) }}@if(!empty(request()->lang)){{ '?lang=' . request()->lang }}@endif"
                   class="boutik-topbar-link">
                    {{ __('business.sign_in') }}
                </a>
            @endif
            <span class="boutik-lang-toggle">
                @include('layouts.partials.language_btn')
            </span>
        </div>
    </header>

    {{-- Contenu principal centré verticalement --}}
    <main class="boutik-auth-wrap">
        <div style="width:100%; max-width:440px;">
            @yield('content')
        </div>
    </main>

    {{-- Mention pied de page --}}
    <div class="boutik-footer-mention">
        © {{ date('Y') }} Boutik Cameroun · Solution Artistik · Tous droits réservés
    </div>

    @include('layouts.partials.javascripts')
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();
        });
    </script>
</body>
</html>
