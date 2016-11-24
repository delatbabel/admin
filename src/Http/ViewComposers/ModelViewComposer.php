<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ModelViewComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'model index view');

        // get a model instance that we'll use for constructing stuff
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var \DDPro\Admin\Config\Model\Config $config */
        $config = app('itemconfig');

        /** @var FieldFactory $fieldFactory */
        $fieldFactory = app('admin_field_factory');

        /** @var ColumnFactory $columnFactory */
        $columnFactory = app('admin_column_factory');

        /** @var ActionFactory $actionFactory */
        $actionFactory = app('admin_action_factory');

        /** @var DataTable $dataTable */
        $dataTable = app('admin_datatable');

        $model = $config->getDataModel();
        $baseUrl = route('admin_dashboard');
        $route = parse_url($baseUrl);

        // add the view fields
        $view->config = $config;
        $view->dataTable = $dataTable;
        $view->primaryKey = $model->getKeyName();
        $view->editFields = $fieldFactory->getEditFields();
        $view->arrayFields = $fieldFactory->getEditFieldsArrays();
        $view->dataModel = $fieldFactory->getDataModel();
        $view->columnModel = $columnFactory->getColumnOptions();
        $view->columnOptions = $columnFactory->getColumnsForDataTable();
        $view->actions = $actionFactory->getActionsOptions();
        $view->globalActions = $actionFactory->getGlobalActionsOptions();
        $view->actionPermissions = $actionFactory->getActionPermissions();
        $view->filters = $fieldFactory->getFiltersArrays();
        // $view->rows = $dataTable->getRows(app('db'), $view->filters);
        $view->formWidth = $config->getOption('form_width');
        $view->baseUrl = $baseUrl;
        $view->assetUrl = url('packages/ddpro/admin/');
        $view->route = $route['path'] . '/';
        $view->itemId = isset($view->itemId) ? $view->itemId : null;
    }
}
