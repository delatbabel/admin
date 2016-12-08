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
<body>
<div id="wrapper">
    @include('adminlayouts.sidebar')
    <div id="page-wrapper" class="gray-bg">
        @include('adminlayouts.header')
        @yield('content')
        @include('adminlayouts.footer')
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
