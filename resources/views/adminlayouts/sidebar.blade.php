<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold">David Williams</strong>
                            </span>
                            <span class="text-muted text-xs block">
                                Art Director <b class="caret"></b>
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li>
                            <a href="{{url(config('administrator.logout_path'))}}">Logout</a>
                        </li>
                    </ul>
                </div>
                <div class="logo-element">IN+</div>
            </li>
            {{-- Sidebar Menu --}}
            @foreach ($menu as $key => $item)
                @include('adminlayouts.menu_item')
            @endforeach
            {{-- Centaur Routes --}}
            @if (Sentinel::check() && Sentinel::inRole('administrator'))
                <li class="{{ Request::is('users*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="fa fa-magic"></i>
                        <span class="nav-label">Users</span>
                    </a>
                </li>
                <li class="{{ Request::is('roles*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}">
                        <i class="fa fa-magic"></i>
                        <span class="nav-label">Roles</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
