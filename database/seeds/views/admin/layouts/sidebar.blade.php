{{-- Left side column. contains the logo and sidebar --}}
@inject('user', 'DDPro\Admin\Services\User')
<?php $sentinelUser = $user->getUser() ?>
<aside class="main-sidebar">

    {{-- sidebar: style can be found in sidebar.less --}}
    <section class="sidebar">

        {{-- Sidebar user panel (optional) --}}
        <div class="user-panel">
            <div class="pull-left image">
                <img src="//www.gravatar.com/avatar/{{ md5($user->getUser()->email) }}?d=mm" alt="{{ $user->getUser()->email }}" class="img-circle">
            </div>
            <div class="pull-left info">
                <p>
                    @if ($user->fullName())
                        {{ $user->fullName() }}
                    @else
                        {{ $sentinelUser->email }}
                    @endif
                </p>
                {{-- Status --}}
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        {{-- search form (Optional) --}}
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
          <span class="input-group-btn">
            <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
          </span>
            </div>
        </form>
        {{-- /.search form --}}

        {{-- Sidebar Menu --}}
        <ul class="sidebar-menu">

            @foreach ($menu as $key => $item)
                @include('admin.layouts.menu_item')
            @endforeach

            @if ($sentinelUser->hasAccess(['all.all']))
                <li><a href="{{ url(config('administrator.uri', 'admin').'/backup') }}"><i class="fa fa-hdd-o"></i> <span>Backups</span></a></li>
                <li><a href="{{ url(config('administrator.uri', 'admin').'/log') }}"><i class="fa fa-terminal"></i> <span>Logs</span></a></li>
                <li>
                    <a href="/sysadmin/files">
                        <i class="fa fa-file"></i>
                        <span class="nav-label">File Manager</span>
                    </a>
                </li>
            @endif
        </ul>{{-- /.sidebar-menu --}}

    </section>
    {{-- /.sidebar --}}
</aside>
<?php
$check_active       = false;
$current_route_name = Route::current()->getName();
if ($current_route_name != 'admin_dashboard') {
    $check_active = true;
    $current_url  = url(Route::current()->getUri());
    if (strpos($current_url, '{model}') !== false) {
        $current_model = Route::current()->model;
        $current_url   = str_replace('{model}', Route::current()->model, url(Route::current()->getUri()));
    }
}
?>
@if ($check_active && ! empty($current_url))
<?php
// Write session variable so check_active.js knows which URL is current
session(['current_url'  => $current_url]);
?>
<script type="text/javascript" src="{{ asset('parts.js.check_active.js') }}"></script>
@endif
