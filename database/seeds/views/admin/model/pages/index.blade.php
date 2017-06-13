@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h2>
                {{$sTitle or 'Customize View'}}
                <small>{{ $config->getOption('title') }}</small>
            </h2>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active"><strong>Here</strong></li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                @include('admin.model.table')
            </div>
        </section>
    </div>
@endsection