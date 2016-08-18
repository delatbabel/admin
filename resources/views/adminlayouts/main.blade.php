<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('administrator.title') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.4 -->
    <link href="{{ asset('packages/ddpro/admin/bower_components/admin-lte/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="{{ asset('packages/ddpro/admin/bower_components/fontawesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="{{ asset('packages/ddpro/admin/bower_components/Ionicons/css/ionicons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="{{ asset('packages/ddpro/admin/bower_components/admin-lte/dist/css/AdminLTE.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect.
    -->
    <link href="{{ asset('packages/ddpro/admin/bower_components/admin-lte/dist/css/skins/skin-blue.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="{{ asset('packages/ddpro/admin/bower_components/admin-lte/plugins/iCheck/square/blue.css') }}" rel="stylesheet" type="text/css" />

{{-- I have removed these for IE8 support because I don't really believe that anyone
     who is going to be doing site admin is ever going to use IE8. By all means feel
     free to add it back in again if your users are still in the stone age.

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
--}}

{{-- This is where the CSS files for DDPro Admin get inserted.  These get created in the
     setViewComposers() function in AdminServiceProvider --}}
{{--  TODO -- move all of the above CSS assets into AdminServiceProvider --}}
@foreach ($css as $url)
    <link href="{{$url}}" media="all" type="text/css" rel="stylesheet">
@endforeach
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="skin-blue sidebar-mini">
<div class="wrapper">

    <!-- Main Header -->
    @include('adminlayouts.header')

    <!-- Left side column. contains the logo and sidebar -->
    @include('adminlayouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    @yield('content')

    <!-- Main Footer -->
    @include('adminlayouts.footer')

    <!-- Control Sidebar -->
    @include('adminlayouts.control')
</div><!-- ./wrapper -->

{{--  REQUIRED JS SCRIPTS -- see AdminServiceProvider --}}

{{-- This is where the JS files for DDPro Admin get inserted.  These get created in the
     setViewComposers() function in AdminServiceProvider --}}
@foreach ($js as $url)
    <script src="{{$url}}"></script>
@endforeach

{{-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. Slimscroll is required when using the
     fixed layout. --}}
</body>
</html>
