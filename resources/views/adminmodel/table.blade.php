@if ($config->getOption('server_side'))
    @include('adminmodel.filters')
@endif
<div class="ibox float-e-margins">
    <div class="ibox-title">
        <div class="ibox-tools">
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
    <div class="ibox-content">
        <div class="table-responsive">
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
</div>
@section('javascript')
    @parent
    <script type="text/javascript">
        // Set up the DataTable in table.blade.php
        var ListPageHandle = function () {
            var TableHandle = function () {
                var ajaxParams = {};
                return {
                    init: function (options) {
                        if (!$().dataTable) {
                            return;
                        }
                        var the = this;
                        // default settings
                        options = $.extend(true, {
                            src: '', // table selector
                            filterApplyAction: '',
                            filterCancelAction: '',
                            dataTable: {
                                ajax: { // define ajax settings
                                    "type": "POST", // request type
                                    "data": function (data) { // add request parameters before submit
                                        $.each(ajaxParams, function (key, value) {
                                            data[key] = value;
                                        });
                                    }
                                },
                                columns: {},
                                aoColumnDefs: [
                                    {
                                        targets: -1,
                                        render: function (data, type, col) {
                                            var primaryCol = the.getPrimaryKeyCol();
                                            if (primaryCol != null) {
                                                var tmpEditURL = "{!! $route.$config->getOption('name') !!}" + '/' + col[primaryCol];
                                                var _tmpButtons = "<a style='margin: 0px' class='btn btn-xs btn-success' href='" + tmpEditURL + "'><i class='fa fa-pencil'></i> Edit</a> ";
                                                _tmpButtons += "<button style='margin: 0px' class='btn btn-xs btn-danger btn-remove' data-id='" + col[primaryCol] + "'><i class='fa fa-trash'></i> Delete</button>";
                                                return _tmpButtons;
                                            }
                                            else {
                                                return null;
                                            }
                                        }
                                    }
                                ],
                                buttons: []
                            }
                        }, options);

                        options.dataTable.columns.push({
                            name: 'tmpActions',
                            orderable: false
                        });

                        var $table = $(options.src);
                        var dataTable = $table.DataTable(options.dataTable);

                        $table.on('click', 'button.btn-remove', function () {
                            if (!window.confirm('Are you sure you want to delete this item? This cannot be reversed.')) {
                                return;
                            }
                            var tmpURL = "{!! $route.$config->getOption('name') !!}" + '/' + $(this).data('id') + '/delete';
                            $.ajax({
                                url: tmpURL,
                                method: 'POST'
                            }).done(function (data) {
                                if (data.success) {
                                    dataTable.ajax.reload();
                                }
                                else {
                                    alert(data.error);
                                }
                            });
                        });
                        $(options.filterApplyAction).click(function () {
                            // get all typeable inputs
                            $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])').each(function () {
                                the.setAjaxParam($(this).attr("name"), $(this).val());
                            });
                            // get all checkboxes
                            $('input.form-filter[type="checkbox"]:checked').each(function () {
                                the.addAjaxParam($(this).attr("name"), $(this).val());
                            });
                            // get all radio buttons
                            $('input.form-filter[type="radio"]:checked').each(function () {
                                the.setAjaxParam($(this).attr("name"), $(this).val());
                            });
                            dataTable.ajax.reload();
                        });
                        $(options.filterCancelAction).click(function () {
                            $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])')
                                .not(':button, :submit, :reset, :hidden')
                                .val('')
                                .removeAttr('checked')
                                .removeAttr('selected');
                            $(".select2").val('').trigger('change');

                            the.clearAjaxParams();
                            dataTable.ajax.reload();
                        });
                    },
                    getPrimaryKeyCol: function () {
                        var ColNum = null;
                        var column_model = {!! json_encode($columnModel) !!};
                        var primary_key = "{!! $primaryKey !!}";
                        $.each(column_model, function (key, obj) {
                            if (obj.column_name == primary_key) {
                                ColNum = key;
                            }
                        });
                        return ColNum;
                    },
                    setAjaxParam: function (name, value) {
                        ajaxParams[name] = value;
                    },
                    addAjaxParam: function (name, value) {
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
                    },
                    clearAjaxParams: function (name, value) {
                        ajaxParams = {};
                    }
                }
            }()

            return {
                init: function (options) {
                    TableHandle.init(options);
                }
            };
        }();
        $(function () {
            ListPageHandle.init({
                src: '#customers',
                filterApplyAction: '.btn-filter',
                filterCancelAction: '.btn-filter-reset',
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
