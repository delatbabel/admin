@extends('adminlayouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
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

        <!-- Main content -->
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <div id="sidebar">
                    <div class="panel sidebar_section" id="filters_sidebar_section" data-bind="template: 'filtersTemplate'"></div>
                </div>
                <div id="content" data-bind="template: 'adminTemplate'"></div>
            </div>
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <script type="text/javascript">
        var site_url = "{!! url('/') !!}",
            base_url = "{!! $baseUrl !!}/",
            asset_url = "{!! $assetUrl !!}",
            file_url = "{!! route('admin_display_file', array($config->getOption('name'))) !!}",
            rows_per_page_url = "{!! route('admin_rows_per_page', array($config->getOption('name'))) !!}",
            route = "{!! $route !!}",
            csrf = "{!! csrf_token() !!}",
            language = "{!! config('app.locale') !!}",
            adminData = {
                primary_key: "{!! $primaryKey !!}",
                @if ($itemId !== null)
                id: "{!! $itemId !!}",
                @endif
                rows: {!! json_encode($rows) !!},
                rows_per_page: {!! $dataTable->getRowsPerPage() !!},
                sortOptions: {!! json_encode($dataTable->getSort()) !!},
                model_name: "{!! $config->getOption('name') !!}",
                model_title: "{!! $config->getOption('title') !!}",
                model_single: "{!! $config->getOption('single') !!}",
                expand_width: {!! $formWidth !!},
                actions: {!! json_encode($actions) !!},
                global_actions: {!! json_encode($globalActions) !!},
                filters: {!! json_encode($filters) !!},
                edit_fields: {!! json_encode($arrayFields) !!},
                data_model: {!! json_encode($dataModel) !!},
                column_model: {!! json_encode($columnModel) !!},
                action_permissions: {!! json_encode($actionPermissions) !!},
                languages: {!! json_encode(trans('administrator::knockout')) !!}
            };
    </script>

    <style type="text/css">

        div.item_edit form.edit_form select, div.item_edit form.edit_form input[type=hidden], div.item_edit form.edit_form .select2-container {
            width: {{ $formWidth - 59 }}px !important;
        }

        div.item_edit form.edit_form .cke {
            width: {{ $formWidth - 67 }}px !important;
        }

        div.item_edit form.edit_form div.markdown textarea {
            width: {{ intval(($formWidth - 75) / 2) - 12 }}px !important;
            max-width: {{ intval(($formWidth - 75) / 2) - 12 }}px !important;
        }

        div.item_edit form.edit_form div.markdown div.preview {
            width: {{ intval(($formWidth - 75) / 2) }}px !important;
        }

        div.item_edit form.edit_form input[type="text"], div.item_edit form.edit_form input[type="password"], div.item_edit form.edit_form textarea {
            max-width: {{ $formWidth - 75 }}px !important;
            width: {{ $formWidth - 75 }}px !important;
        }

        div.item_edit form.edit_form > div.image img, div.item_edit form.edit_form > div.image div.image_container {
            max-width: {{ $formWidth - 65 }}px;
        }

    </style>

    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <script id="adminTemplate" type="text/html">
    @include('adminmodel.table')
    </script>

    <script id="itemFormTemplate" type="text/html">
    @include('adminmodel.edit')
    </script>

    <script id="filtersTemplate" type="text/html">
    @include('adminmodel.filters')
    </script>

@endsection
