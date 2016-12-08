@extends('adminlayouts.main')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>
                {{ Config::get('administrator.title') }}
                <small>{{ $config->getOption('title') }}</small>
            </h2>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active"><strong>Here</strong></li>
            </ol>
        </div>
    </div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <?php $tmpRouteName = Route::getCurrentRoute()->getName(); ?>
                @if( $tmpRouteName == 'admin_index')
                    @include('adminmodel.table')
                @else
                    @include('adminmodel.form')
                @endif
            </div>
        </div>
    </div>
@endsection
