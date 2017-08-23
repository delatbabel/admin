@if ($config->getOption('server_side') && !empty($filters))
    @include('admin.model.filters')
@endif
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $config->getOption('single') }} List</h3>
        <div class="box-tools pull-right">
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
        <table class="table table-striped table-bordered table-hover dataTables-example responsive" width="100%" id="customers">
            <thead>
            <tr>
                @foreach($columnModel as $tmpArr)
                    <th>{{$tmpArr['title']}}</th>
                @endforeach
                <th>Actions</th>
            </tr>
            </thead>
            @if ($config->getOption('deletable') || $config->getOption('activation') || !empty($globalActions))
            <tfoot>
            <tr>
                <th><input id="selectAll" value="1" type="checkbox"></th>
                <th colspan="{{count($columnModel)}}">
                    @if ($config->getOption('deletable'))
                        <button class="btn btn-sd-normal"
                                data-url="{!! route('admin_destroy_items', [$config->getOption('name')]) !!}"
                                data-confirmation="Are you sure you want to do this?"
                                data-messages='{{ json_encode(array('active'=>'Doing...', 'success'=>'Done', 'error'=>'There was an error while doing')) }}'>
                            Delete</button>
                    @endif
                    @if ($config->getOption('activation'))
                        <button class="btn btn-sd-normal"
                                data-url="{!! route('admin_toggle_activate_items', [$config->getOption('name')]) !!}"
                                data-params='{{ json_encode(array('status'=>'active')) }}'
                                data-confirmation="Are you sure you want to do this?"
                                data-messages='{{ json_encode(array('active'=>'Doing...', 'success'=>'Done', 'error'=>'There was an error while doing')) }}'>
                                Activate
                        </button>
                        <button class="btn btn-sd-normal"
                                data-url="{!! route('admin_toggle_activate_items', [$config->getOption('name')]) !!}"
                                data-params='{{ json_encode(array('status'=>'inactive')) }}'
                                data-confirmation="Are you sure you want to do this?"
                                data-messages='{{ json_encode(array('active'=>'Doing...', 'success'=>'Done', 'error'=>'There was an error while doing')) }}'>
                                Deactivate
                        </button>
                    @endif
                    @foreach($globalActions as $arr)
                        <button class="btn btn-sd-normal"
                                id="{{$arr['action_name']}}"
                                @if(isset($arr['url'])) data-url="{{$arr['url']}}" @else data-url="{!! route('admin_custom_model_action', [$config->getOption('name')]) !!}" @endif
                                @if(isset($arr['params'])) data-params='{{json_encode($arr['params'])}}' @endif
                                @if(isset($arr['confirmation'])) data-confirmation="{{$arr['confirmation']}}" @endif
                                data-messages='{{json_encode($arr['messages'])}}'>
                                {{$arr['title']}}
                        </button>
                    @endforeach
                    <div class="inside-pagination"></div>
                </th>
            </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
<?php
    // Custom buttons
    $buttons = [];
    if (null !== $config->getOption('item_actions')) {
        foreach ($config->getOption('item_actions') as $button) {
            if (!isset($button['url'])) {
                $button['url'] = route('admin_custom_model_action', [$config->getOption('name')]);
            }
            $buttons[] = $button;
        }
    }
?>
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
                //Apply for the custom buttons
                ,buttons: {!! json_encode($buttons) !!},
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
