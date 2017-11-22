<?php
namespace Delatbabel\Admin\Http\ViewComposers;

use Illuminate\View\View;

/**
 * Class MainViewComposer
 * @package Delatbabel\Admin\Http\ViewComposers
 */
class MainViewComposer extends ViewComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $assets = config('administrator.main-assets');

        // Load the CSS and JS files as defined in the config.
        $this->generateAssets($view, $assets);
    }
}
