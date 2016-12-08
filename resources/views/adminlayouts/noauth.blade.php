<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('administrator.title') }}</title>
    @if(isset($css) && is_array($css))
        @foreach ($css as $tmpURL)
            <link href="{{$tmpURL}}" type="text/css" rel="stylesheet">
        @endforeach
    @endif
    {{-- Custom/Add Special Styles --}}
    @yield('stylesheet')
</head>
<body class="gray-bg">
<div class="@yield('class-wrapper-contents', 'passwordBox animated fadeInDown')">
    @yield('content')
    <hr/>
    <div class="row">
        <div class="col-md-7">
            Copyright &copy; 2016
            <a href="{{ config('administrator.company_url') }}">
                {{ config('administrator.company_name') }}
            </a>.
        </div>
        <div class="col-md-5 text-right">
            <small>All rights reserved</small>
        </div>
    </div>
</div>
@if(isset($js) && is_array($js))
    @foreach ($js as $tmpURL)
        <script src="{{$tmpURL}}"></script>
    @endforeach
@endif
{{-- Custom/Add Special Scripts --}}
@yield('javascript')
</body>
</html>
