<!-- Left side column. contains the logo and sidebar -->
@inject('user', 'DDPro\Admin\Services\User')
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="//www.gravatar.com/avatar/{{ md5($user->getUser()->email) }}?d=mm" alt="{{ $user->getUser()->email }}" class="img-circle">
            </div>
            <div class="pull-left info">
                <p>
                    @if ($user->fullName())
                        {{ $user->fullName() }}
                    @else
                        {{ $user->getUser()->email }}
                    @endif
                </p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
          <span class="input-group-btn">
            <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
          </span>
            </div>
        </form>
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header">Admin Menu</li>
            <li><a href="{{ url(config('administrator.uri', 'admin').'/backup') }}"><i class="fa fa-hdd-o"></i> <span>Backups</span></a></li>
            @foreach ($menu as $key => $item)
                @include('admin.layouts.menu_item')
            @endforeach
        </ul><!-- /.sidebar-menu -->

    </section>
    <!-- /.sidebar -->
</aside>
<?php
$check_active = false;
$current_route_name = Route::current()->getName();
if ($current_route_name != 'admin_dashboard') {
    $check_active = true;
    $current_url = url(Route::current()->getUri());
    if (strpos($current_url, '{model}') !== false) {
        $current_model = Route::current()->model;
        $current_url = str_replace('{model}', Route::current()->model, url(Route::current()->getUri()));
    }
}
?>
@if ($check_active)
    <script type="text/javascript">
        // Set active state on menu element
        var current_url = "{{ $current_url }}";
        $("ul.sidebar-menu li a").each(function() {
            if ($(this).attr('href').startsWith(current_url) || current_url.startsWith($(this).attr('href')))
            {
                $(this).parents('li').addClass('active');
            }
        });
    </script>
@endif