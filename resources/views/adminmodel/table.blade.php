@if ($config->getOption('server_side') && !empty($filters))
    @include('adminmodel.filters')
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
                        "url": "{!! route('admin_get_datatable_results', [$config->getOption('name')]) !!}",
                    },
                    "columns": {!! json_encode($columnOptions) !!},
                    dom: '<"html5buttons"B>lTfgitp'
                }
            });
        });
    </script>
@endsection
