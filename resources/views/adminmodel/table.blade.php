<div class="table_container">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title" data-bind="text: modelTitle"></h3>

            <div class="actions">
                <!-- ko if: globalActions().length -->
                    <!-- ko foreach: globalActions -->
                        <!-- ko if: has_permission -->
                            <input type="button" data-bind="click: function(){$root.customAction(false, action_name, messages, confirmation)}, value: title,
                                                                            attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
                        <!-- /ko -->
                    <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: actionPermissions.create -->
                    <a class="btn btn-block btn-primary"
                        data-bind="attr: {href: base_url + modelName() + '/new'},
                                    text: '<?php echo trans('administrator::administrator.new') ?> ' + modelSingle()"></a>
                <!-- /ko -->
            </div>
        </div>

        <!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <!-- ko foreach: columns -->
                        <th data-bind="visible: visible, css: {sortable: sortable,
                            'sorted-asc': (column_name == $root.sortOptions.field() || sort_field == $root.sortOptions.field()) && $root.sortOptions.direction() === 'asc',
                            'sorted-desc': (column_name == $root.sortOptions.field() || sort_field == $root.sortOptions.field()) && $root.sortOptions.direction() === 'desc'}">
                            <!-- ko if: sortable -->
                                <div data-bind="click: function() {$root.setSortOptions(sort_field ? sort_field : column_name)}, text: title"></div>
                            <!-- /ko -->

                            <!-- ko ifnot: sortable -->
                                <div data-bind="text: title"></div>
                            <!-- /ko -->
                        </th>
                    <!-- /ko -->
                    </tr>
                </thead>
                <tbody>
                <!-- ko foreach: rows -->
                    <tr data-bind="click: function() {$root.clickItem($data[$root.primaryKey].raw); return true},
                                css: {result: true, even: $index() % 2 == 1, odd: $index() % 2 != 1,
                                        selected: $data[$root.primaryKey].raw == $root.itemLoadingId()}">
                        <!-- ko foreach: $root.columns -->
                            <td data-bind="html: $parentContext.$data[column_name].rendered, visible: visible"></td>
                        <!-- /ko -->
                    </tr>
                <!-- /ko -->
                </tbody>
            </table>
        </div>

        <div class="loading_rows" data-bind="visible: loadingRows">
            <div><?php echo trans('administrator::administrator.loading') ?></div>
        </div>
    </div>
</div>
    
<div class="item_edit_container" data-bind="itemTransition: activeItem() !== null || loadingItem(), style: {width: expandWidth() + 'px'}">
    <div class="item_edit" data-bind="template: 'itemFormTemplate', style: {width: (expandWidth() - 27) + 'px'}"></div>
</div>
