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
                @if ($config->getOption('server_side'))
                    <div class="row">
                        <div class="col-md-12">
                            <div id="sidebar">
                                <div class="sidebar_section" id="filters_sidebar_section">
                                    @include('adminmodel.filters')
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div id="content">@include('adminmodel.table')</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script type="text/javascript">
        // Set up the DataTable in table.blade.php
        $(function () {
            var ajaxParams = {};
            var tmpDatatable = $("#customers").DataTable({
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
                    "type": "POST",
                    "data": function (data) { // add request parameters before submit
                        $.each(ajaxParams, function (key, value) {
                            data[key] = value;
                        });
                    },
                },
                "columns": {!! json_encode($columnOptions) !!}
            }).on('select', function (e, dt, type, indexes) {
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
            }).on('deselect', function (e, dt, type, indexes) {
                $('a.edit_item').hide();
            }).on('draw.dt', function () {
                $('a.edit_item').hide();
            });
            $('.btn-filter').click(function () {
                // get all typeable inputs
                $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])').each(function () {
                    setAjaxParam($(this).attr("name"), $(this).val());
                });
                // get all checkboxes
                $('input.form-filter[type="checkbox"]:checked').each(function () {
                    addAjaxParam($(this).attr("name"), $(this).val());
                });
                // get all radio buttons
                $('input.form-filter[type="radio"]:checked').each(function () {
                    setAjaxParam($(this).attr("name"), $(this).val());
                });
                tmpDatatable.ajax.reload();
            });
            $('.btn-filter-reset').click(function () {
                $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])')
                    .not(':button, :submit, :reset, :hidden')
                    .val('')
                    .removeAttr('checked')
                    .removeAttr('selected');
                $(".select2").val('').trigger('change');

                clearAjaxParams();
                tmpDatatable.ajax.reload();
            });
            function setAjaxParam(name, value) {
                ajaxParams[name] = value;
            }

            function addAjaxParam(name, value) {
                if (!ajaxParams[name]) {
                    ajaxParams[name] = [];
                }
                skip = false;
                for (var i = 0; i < (ajaxParams[name]).length; i++) { // check for duplicates
                    if (ajaxParams[name][i] === value) {
                        skip = true;
                    }
                }
                if (skip === false) {
                    ajaxParams[name].push(value);
                }
            }

            function clearAjaxParams(name, value) {
                ajaxParams = {};
            }
        });
    </script>
@endsection
