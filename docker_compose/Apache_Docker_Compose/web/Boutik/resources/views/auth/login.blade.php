@extends('layouts.auth2')
@section('title', __('lang_v1.login'))
@inject('request', 'Illuminate\Http\Request')
@section('content')
    @php
        $username = old('username');
        $password = null;
        if (config('app.env') == 'demo') {
            $username = 'admin';
            $password = '123456';
        }
    @endphp

    <div class="boutik-auth-card">
        {{-- En-tête de carte avec logo (charte teal) --}}
        <div class="card-head">
            <div class="logo-circle">
                <img src="{{ asset('img/boutik/logo-mark.svg') }}" alt="Boutik" width="72" height="72">
            </div>
            <h1 class="card-title">@lang('lang_v1.welcome_back')</h1>
            <p class="card-subtitle">
                @lang('lang_v1.login_to_your') {{ config('app.name', 'Boutik') }}
            </p>
        </div>

        <div style="padding: 28px 28px 24px;">
            <form method="POST" action="{{ route('login') }}" id="login-form" style="display:flex; flex-direction:column; gap:18px;">
                {{ csrf_field() }}

                {{-- Nom d'utilisateur --}}
                <div class="form-group {{ $errors->has('username') ? ' has-error' : '' }}" style="margin-bottom:0;">
                    <label for="username" class="field-label">@lang('lang_v1.username')</label>
                    <input class="field-input"
                           id="username" type="text" name="username" required autofocus
                           placeholder="@lang('lang_v1.username')"
                           value="{{ $username }}">
                    @if ($errors->has('username'))
                        <span class="help-block" style="color:#dc2626; font-size:13px; margin-top:4px; display:block;">
                            <strong>{{ $errors->first('username') }}</strong>
                        </span>
                    @endif
                </div>

                {{-- Mot de passe --}}
                <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}" style="margin-bottom:0; position:relative;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <label for="password" class="field-label" style="margin-bottom:0;">@lang('lang_v1.password')</label>
                        @if (config('app.env') != 'demo')
                            <a href="{{ route('password.request') }}" class="boutik-link" style="font-size:12px;"
                               tabindex="-1">@lang('lang_v1.forgot_your_password')</a>
                        @endif
                    </div>
                    <input class="field-input"
                           id="password" type="password" name="password" required
                           placeholder="@lang('lang_v1.password')"
                           value="{{ $password }}" style="padding-right:42px;">
                    <button type="button" id="show_hide_icon"
                            style="position:absolute; top:30px; right:10px; background:transparent; border:none; cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye"
                             width="22" height="22" viewBox="0 0 24 24" stroke-width="1.7"
                             stroke="#0e7490" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>
                        </svg>
                    </button>
                    @if ($errors->has('password'))
                        <span class="help-block tw-text-red-600 tw-text-sm tw-mt-1">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>

                {{-- Souviens-toi de moi --}}
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                           style="width:16px; height:16px; accent-color:#0e7490; margin:0;">
                    <span style="font-size:14px; font-weight:500; color:#475569;">
                        @lang('lang_v1.remember_me')
                    </span>
                </label>

                @if(config('constants.enable_recaptcha'))
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="{{ config('constants.google_recaptcha_key') }}"></div>
                        @if ($errors->has('g-recaptcha-response'))
                            <span class="text-danger tw-text-sm">{{ $errors->first('g-recaptcha-response') }}</span>
                        @endif
                    </div>
                @endif

                <button type="submit" class="boutik-btn-primary" style="margin-top:6px;">
                    @lang('lang_v1.login')
                </button>
            </form>

            {{-- Lien inscription --}}
            @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                @if (config('constants.allow_registration'))
                    <p style="text-align:center; margin:20px 0 0; font-size:14px; color:#475569;">
                        {{ __('business.not_yet_registered') }}
                        <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{ '?lang=' . request()->lang }}@endif"
                           class="boutik-link" style="margin-left:4px;">{{ __('business.register_now') }}</a>
                    </p>
                @endif
            @endif
        </div>
    </div>

@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#show_hide_icon').off('click');
            $('.change_lang').click(function() {
                window.location = "{{ route('login') }}?lang=" + $(this).attr('value');
            });

            $('#show_hide_icon').on('click', function(e) {
                e.preventDefault();
                const passwordInput = $('#password');
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    $('#show_hide_icon').html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="22" height="22" viewBox="0 0 24 24" stroke-width="1.7" stroke="#0e7490" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>');
                } else {
                    passwordInput.attr('type', 'password');
                    $('#show_hide_icon').html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="22" height="22" viewBox="0 0 24 24" stroke-width="1.7" stroke="#0e7490" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>');
                }
            });
        });
    </script>
@endsection
