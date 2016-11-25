@extends('adminlayouts.main')
@include('adminmodel.contacts.stamp') {{-- Will be remove --}}
@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <div class="row">
                    <div class="col-md-12">
                        <div id="content">
                            <div class="table_container">
                                <div class="box">
                                    <div class="results_header box-header">
                                        <h2 class="box-title">{{$config->getOption('title')}}</h2>
                                    </div>
                                    <table class="table table-bordered table-striped" border="0" cellspacing="0"
                                           id="customers" cellpadding="0">
                                        <thead>
                                        <tr>
                                            @foreach($columnModel as $tmpArr)
                                                <th>{{$tmpArr['title']}}</th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('adminmodel.contacts.form')
                    </div>
                </div>
            </div>
        </section>
    </div>
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
