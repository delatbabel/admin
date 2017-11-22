<?php
namespace Delatbabel\Admin\Http\ViewComposers;

use Illuminate\View\View;

/**
 * Class NoauthViewComposer
 * @package Delatbabel\Admin\Http\ViewComposers
 */
class NoauthViewComposer extends ViewComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $assets = config('administrator.noauth-assets');

        // Load the CSS and JS files as defined in the config.
        $this->generateAssets($view, $assets);
    }
}
