<?php
namespace Delatbabel\Admin\Http\ViewComposers;

use Illuminate\View\View;

/**
 * Class SettingViewComposer
 * @package Delatbabel\Admin\Http\ViewComposers
 */
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
        /** @var \Delatbabel\Admin\Fields\Factory $fieldFactory */
        $fieldFactory = app('admin_field_factory');

        /** @var \Delatbabel\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $baseUrl = route('admin_dashboard');
        $route   = parse_url($baseUrl);

        // add the view fields
        $view->config      = $config;
        $view->editFields  = $fieldFactory->getEditFields();
        $view->arrayFields = $fieldFactory->getEditFieldsArrays();
        $view->actions     = $actionFactory->getActionsOptions();
        $view->baseUrl     = $baseUrl;
        $view->assetUrl    = url('packages/ddpro/admin/');
        $view->route       = $route['path'] . '/';
    }
}
