@extends('adminlayouts.main')

@section('admindata')
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
@endsection

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
                {{--
                FIXME: Only want to display filters when server side processing is enabled, but removing
                the filters throws errors in knockout.js caused by admin.js setting the bindings to the
                filters when they are not there.  Best solution is probably to remove knockout.js from
                the filters template completely.
                @if ($config->getOption('server_side'))
                --}}
                <div class="row">
                    <div class="col-md-12">
                        <div id="sidebar">
                            <div class="sidebar_section" id="filters_sidebar_section"
                                 data-bind="template: 'filtersTemplate'"></div>
                        </div>
                    </div>
                </div>
                {{-- @endif --}}
                <div class="row">
                    <div class="col-md-12">
                        <div id="content" data-bind="template: 'adminTemplate'"></div>
                    </div>
                </div>
            </div>
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

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

        {{--div.item_edit form.edit_form input[type="text"], div.item_edit form.edit_form input[type="password"], div.item_edit form.edit_form textarea {--}}
            {{--max-width: {{ $formWidth - 75 }}px !important;--}}
            {{--width: {{ $formWidth - 75 }}px !important;--}}
        {{--}--}}

        div.item_edit form.edit_form > div.image img, div.item_edit form.edit_form > div.image div.image_container {
            max-width: {{ $formWidth - 65 }}px;
        }

    </style>

    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

    <script id="adminTemplate" type="text/html">
    @include('adminmodel.table')
    </script>

    <script id="itemFormTemplate" type="text/html">
    @include('adminmodel.edit')
    </script>

    <script id="filtersTemplate" type="text/html">
    @include('adminmodel.filters')
    </script>

    <script type="text/javascript">
        // Set up the DataTable in table.blade.php
        $(function () {
            $("#customers").DataTable({
                @if ($actionPermissions['update'])
                "deferRender": true,
                "select": 'single',
                @endif
                "processing": true,
                @if ($config->getOption('server_side'))
                "searching": false,
                "serverSide": true,
                @endif
                "ajax": {
                    "url": "{!! route('admin_get_datatable_results', array($config->getOption('name'))) !!}",
                    "type": "POST"
                },
                "columns": {!! json_encode($columnOptions) !!}
            })
                .on('select', function (e, dt, type, indexes) {
                    var rowData = dt.rows(indexes).data().toArray()[0];
                    var flg = false;
                    $.each(adminData.column_model, function (key, obj) {
                        if (obj.column_name == adminData.primary_key) {
                            $('a.edit_item').attr('data-id', rowData[key]).show();
                            flg = true;
                        }
                    });
                    if (!flg) {
                        alert('For edit item, you should add an column for primary key')
                    }
                })
                .on('deselect', function (e, dt, type, indexes) {
                    $('a.edit_item').hide();
                })
                .on('draw.dt', function () {
                    $('a.edit_item').hide();
                });
        });
    </script>

@endsection
