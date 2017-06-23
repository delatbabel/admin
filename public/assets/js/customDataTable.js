// Set up the DataTable in table.blade.php
var DataTableHandle = function () {
    var options = null;
    var ajaxParams = null;
    var the = null;
    return {
        init: function (tmpOptions) {
            if (!$().dataTable) {
                return;
            }
            // default settings
            options = $.extend(true, {
                src: null, // table selector
                filterApplyAction: null,
                filterCancelAction: null,
                editURL: null,
                deleteURL: null,
                column_model: null,
                primary_key: null,
                defaultParams: {},
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
                    aaSorting: [],
                    aoColumnDefs: [
                        {
                            targets: -1,
                            render: function (data, type, col) {
                                var primaryCol = the.getPrimaryKeyCol();
                                if (primaryCol != null) {
                                    var tmpEditURL = options.editURL + '/' + col[primaryCol];
                                    var _tmpButtons = "<a class='btn btn-xs btn-sd-default' href='" + tmpEditURL + "'>Edit</a> ";
                                    _tmpButtons += "<button class='btn btn-xs btn-sd-default removeBtn' data-id='" + col[primaryCol] + "'>Delete</button>";
                                    return _tmpButtons;
                                }
                                else {
                                    return null;
                                }
                            }
                        }
                    ],
                    createdRow: function( row, data, dataIndex ) {
                        primaryCol = the.getPrimaryKeyCol();
                        if (primaryCol != null) {
                            $(row).attr('data-id', data[primaryCol]);
                        }
                    },
                    drawCallback: function() { // Do not display tfoot if datatable is empty
                        if(this.api().rows().count() === 0) {
                            this.find('tfoot').hide();
                        } else {
                            this.find('tfoot').show();
                        }
                    },
                    buttons: []
                }
            }, tmpOptions);

            the = this;
            the.initAjaxParams();

            options.dataTable.columns.push({
                name: 'tmpActions',
                orderable: false
            });

            var $table = $(options.src);
            var dataTable = $table.DataTable(options.dataTable);

            if (dataTable.column('lft:name').length > 0) {
                dataTable.order( [ dataTable.column('lft:name').index(), 'desc' ] );
                dataTable.draw();
            }

            $table.on('click', 'button.removeBtn', function () {
                if (!window.confirm('Are you sure you want to delete this item? This cannot be reversed.')) {
                    return;
                }
                $.ajax({
                    url: options.deleteURL + '/' + $(this).data('id') + '/delete',
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

            // Select all button clicked
            $table.on('click', '#selectAll', function () {
                $table.find('tbody input[type="checkbox"]').prop('checked', this.checked);
            });

            // Batch delete items
            $table.on('click', '#batchDelete', function () {
                var ids = [];
                $table.find('tbody input[type="checkbox"]').each(function(){
                    if($(this).is(':checked')) {
                        ids.push($(this).val());
                    }
                });

                if (ids.length > 0) {
                    if (!window.confirm('Are you sure you want to delete selected item(s)? This cannot be reversed.')) {
                        return;
                    }
                    $.ajax({
                        url: options.deleteURL + '/destroy',
                        method: 'POST',
                        data: {ids:ids},
                    }).done(function (data) {
                        if (data.success) {
                            dataTable.ajax.reload();
                            $table.find('#selectAll').prop('checked', false);
                        }
                        else {
                            alert(data.error);
                        }
                    });
                }
            });

            // Batch activate / deactivate items
            $table.on('click', '#batchActivate', function () {
                toogleActivate('active');
            });

            $table.on('click', '#batchDeactivate', function () {
                toogleActivate('inactive');
            });

            function toogleActivate(status) {
                var ids = [];
                $table.find('tbody input[type="checkbox"]').each(function(){
                    if($(this).is(':checked')) {
                        ids.push($(this).val());
                    }
                });

                if (ids.length > 0) {
                    if (!window.confirm('Are you sure?')) {
                        return;
                    }
                    $.ajax({
                        url: options.deleteURL + '/toggle_activate',
                        method: 'POST',
                        data: {status: status, ids:ids},
                    }).done(function (data) {
                        if (data.success) {
                            dataTable.ajax.reload();
                            $table.find('#selectAll').prop('checked', false);
                        }
                        else {
                            alert(data.error);
                        }
                    });
                }
            }

            $(options.filterApplyAction).click(function () {
                the.initAjaxParams();
                dataTable.ajax.reload();
            });
            $(options.filterCancelAction).click(function () {
                $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])')
                    .not(':button, :submit, :reset, :hidden')
                    .val('')
                    .removeAttr('checked')
                    .removeAttr('selected');
                $('.select2-hidden-accessible').val('').trigger('change');

                the.initAjaxParams();
                dataTable.ajax.reload();
            });
        },
        getPrimaryKeyCol: function () {
            var ColNum = null;
            $.each(options.column_model, function (key, obj) {
                if (obj.column_name == options.primary_key) {
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
        initAjaxParams: function () {
            ajaxParams = options.defaultParams;
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
        }
    }
}()
