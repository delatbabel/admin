@if ($config->getOption('server_side') && !empty($filters))
    @include('admin.model.filters')
@endif
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Hover Data Table</h3>
        <div class="box-tools pull-right">
            @foreach($globalActions as $arr)
                @if($arr['has_permission'])
                    <input type="button" class="btn btn-info"
                           value="{{$arr['title']}}"/>
                @endif
            @endforeach
            @if(isset($actionPermissions['update']) === true)
                <a class="edit_item btn btn-primary" style="display: none">
                    {{trans('administrator::administrator.edit')}} {{$config->getOption('single')}}
                </a>
            @endif
            @if ($config->getOption('export') && isset($actionPermissions['export']) === true)
                <a class="btn btn-default"
                   href="{{ route('admin_export', [$config->getOption('name')]) }}">
                    {{ $config->getOption('export')['title'] }}
                </a>
            @endif
            @if(isset($actionPermissions['create']) === true)
                <a class="new_item btn btn-primary"
                   href="{{ route('admin_new_item', [$config->getOption('name')]) }}">
                    {{trans('administrator::administrator.new')}} {{$config->getOption('single')}}
                </a>
            @endif
        </div>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example" id="customers">
            <thead>
            <tr>
                @foreach($columnModel as $tmpArr)
                    <th>{{$tmpArr['title']}}</th>
                @endforeach
                <th>Actions</th>
            </tr>
            </thead>
            @if (array_key_exists('batch_select', $config->getOption('columns')))
            <tfoot>
            <tr>
                <th><input id="selectAll" value="1" type="checkbox"></th>
                <th colspan="{{count($columnModel)}}">
                    <button id="batchDelete" class="btn btn-sd-normal">Delete</button>

                    @if ($config->getOption('activation'))
                        <button id="batchActivate" class="btn btn-sd-normal">Activate</button>
                        <button id="batchDeactivate" class="btn btn-sd-normal">Deactivate</button>
                    @endif
                    <div class="inside-pagination"></div>
                </th>
            </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            DataTableHandle.init({
                src: '#customers',
                filterApplyAction: '.btn-filter',
                filterCancelAction: '.btn-filter-reset',
                editURL: "{!! $route.$config->getOption('name') !!}",
                deleteURL: "{!! $route.$config->getOption('name') !!}",
                column_model: {!! json_encode($columnModel) !!},
                primary_key: "{!! $primaryKey !!}",
                dataTable: {
                    "processing": true,
                    @if ($config->getOption('server_side'))
                    "searching": false,
                    "serverSide": true,
                    @endif
                    "ajax": {
                        "url": "{!! route('admin_get_datatable_results', [$config->getOption('name')]) !!}"
                    },
                    "columns": {!! json_encode($columnOptions) !!},
                    dom: '<"html5buttons"B>lTfgitp'
                }
            });
            $(".inside-pagination").append($("#customers_paginate"));
            $('#customers').on("click", '[data-toggle="ajaxModal"]', function(e) {
                $('#ajaxModal').remove();
                e.preventDefault();
                var $this = $(this),
                        $remote = $this.data('remote') || $this.attr('href'),
                        $modal = $('<div class="modal" id="ajaxModal"><div class="modal-body"></div></div>');
                $("body").append($modal);
                $.ajax({
                    url: $remote,
                    dataType: "json",
                    type: "GET",
                    success: function(result) {
                        if (result.success) {
                            $modal.append(result.html);
                            $modal.modal({backdrop: 'static', keyboard: false, show:true});
                        } else {
                            alert(result.error);
                        }

                    }
                });
            });

            var reordering = "{!! array_key_exists('lft', $config->getOption('columns')) ? true : false !!}";
            if (reordering) {
                $( "#customers tbody" ).sortable({
                    appendTo: document.body,
                    axis: "y",
                    update: function (e, ui) {
                        // send reorder request
                        var id = $(ui.item).data('id');
                        var prev_sibling_id = $(ui.item).prev().data('id');
                        var next_sibling_id = $(ui.item).next().data('id');
                        var dataTable = $('#customers').DataTable();

                        var order = dataTable.order();
                        var defaultOrder = [ dataTable.column('lft:name').index(), 'desc' ];

                        if (order[0].toString() !== defaultOrder.toString()) {
                            alert('Resetting to default order, then you can perform row reordering');
                            dataTable.order(defaultOrder).draw();
                            return;
                        }

                        if (prev_sibling_id || next_sibling_id) {
                            $.ajax({
                                url: "{!! route('admin_reorder_item', ['model' => $config->getOption('name')]) !!}",
                                dataType: "json",
                                type: "POST",
                                data: {
                                    id: id,
                                    prev_sibling_id: prev_sibling_id,
                                    next_sibling_id: next_sibling_id
                                },
                                success: function(result) {
                                    if (!result.success) {
                                        alert(result.error);
                                    }
                                    dataTable.order(defaultOrder).ajax.reload();
                                }
                            });
                        }
                    }
                });
            }
        });
    </script>
@endsection
