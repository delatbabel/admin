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
