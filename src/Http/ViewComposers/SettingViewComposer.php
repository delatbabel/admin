<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

class SettingViewComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $config = app('itemconfig');
        /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
        $fieldFactory = app('admin_field_factory');

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $baseUrl = route('admin_dashboard');
        $route = parse_url($baseUrl);

        // add the view fields
        $view->config = $config;
        $view->editFields = $fieldFactory->getEditFields();
        $view->arrayFields = $fieldFactory->getEditFieldsArrays();
        $view->actions = $actionFactory->getActionsOptions();
        $view->baseUrl = $baseUrl;
        $view->assetUrl = url('packages/ddpro/admin/');
        $view->route = $route['path'] . '/';
    }
}
