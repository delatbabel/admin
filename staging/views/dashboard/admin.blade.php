@extends('layouts.main')
@inject('objects', 'Delatbabel\ViewPages\Services\VobjectService')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ $objects->admin_dashboard_header }}
                <small>{{ $objects->admin_dashboard_description }}</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            @include('dashboard.sample')
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
@endsection
