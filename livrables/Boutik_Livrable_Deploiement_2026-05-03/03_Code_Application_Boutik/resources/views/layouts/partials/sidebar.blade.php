<!-- Left side column. contains the logo and sidebar -->
<aside class="side-bar tw-relative tw-hidden tw-h-full tw-bg-white tw-w-64 xl:tw-w-64 lg:tw-flex lg:tw-flex-col tw-shrink-0">

    <!-- sidebar: style can be found in sidebar.less -->

    {{-- <a href="{{route('home')}}" class="logo">
		<span class="logo-lg">{{ Session::get('business.name') }}</span>
	</a> --}}

    <a href="{{route('home')}}"
        class="tw-flex tw-items-center tw-gap-3 tw-px-3 tw-w-full tw-border-r tw-min-h-[3.75rem] tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-shrink-0 tw-border-primary-500/30">
        <img src="{{ asset('img/boutik/logo-mark.svg') }}" alt="" width="40" height="40" class="tw-shrink-0" style="object-fit:contain;">
        <p class="tw-text-sm tw-font-semibold tw-text-white side-bar-heading tw-text-left tw-leading-tight tw-flex-1 tw-min-w-0 tw-truncate">
            {{ Session::get('business.name') }}
        </p>
        <span class="tw-inline-block tw-w-2.5 tw-h-2.5 tw-bg-emerald-400 tw-rounded-full tw-shrink-0" title="En ligne"></span>
    </a>

    <!-- Sidebar Menu -->
    {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

    <!-- /.sidebar-menu -->
    <!-- /.sidebar -->
</aside>
