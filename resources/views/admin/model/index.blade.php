@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                {{ Config::get('administrator.title') }}
                <small>{{ $config->getOption('title') }}</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <?php $tmpRouteName = Route::getCurrentRoute()->getName(); ?>
                @if( $tmpRouteName == 'admin_index')
                    @include('admin.model.table')
                @else
                    @include('admin.model.form')
                @endif
            </div>
        </section>
    </div>
@endsection
