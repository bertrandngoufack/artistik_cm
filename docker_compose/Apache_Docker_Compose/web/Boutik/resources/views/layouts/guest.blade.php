<!DOCTYPE html>
<html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title> 

    <!-- Tailwind CSS (includes DaisyUI) -->
    <link href="{{ asset('css/tailwind/app.css?v='.$asset_v) }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/vendor.css?v='.$asset_v) }}">

    <!-- app css -->
    <link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">

</head>

<body>
    <div id="app"></div>
    @if (session('status'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
    @endif
    @yield('content')


    <!-- jQuery 2.2.3 -->
    <script src="{{ asset('js/vendor.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/functions.js?v=' . $asset_v) }}"></script>
    @yield('javascript')
</body>

</html>