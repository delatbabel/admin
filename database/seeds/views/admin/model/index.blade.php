@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                {{ Config::get('administrator.title') }}
                <small>{{ $config->getOption('title') }}</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('admin_dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active">{{ $config->getOption('title') }}</li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <?php $tmpRouteName = Route::getCurrentRoute()->getName(); ?>
                @if( $tmpRouteName == 'admin_index')
                    @include($config->getOption('model_table_view'))
                @else
                    @include($config->getOption('model_form_view'))
                @endif
            </div>
        </section>
    </div>
@endsection
