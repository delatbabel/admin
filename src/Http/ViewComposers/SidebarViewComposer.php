<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

class SidebarViewComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->menu = app('admin_menu')->getMenu();
        $view->settingsPrefix = app('admin_config_factory')->getSettingsPrefix();
        $view->pagePrefix = app('admin_config_factory')->getPagePrefix();
        $view->configType = app()->bound('itemconfig') ? app('itemconfig')->getType() : false;
    }
}
