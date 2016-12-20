<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

/**
 * Class MainViewComposer
 * @package DDPro\Admin\Http\ViewComposers
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
        $assets = config('administrator.assets');

        // Load the CSS and JS files as defined in the config.
        foreach ($assets['css']['bower'] as $asset) {
            $view->css[] = $this->bowerAsset($asset);
        }
        foreach ($assets['css']['base'] as $asset) {
            $view->css[] = $this->asset($asset);
        }
        foreach ($assets['js']['bower'] as $asset) {
            $view->js[] = $this->bowerAsset($asset);
        }
        foreach ($assets['js']['base'] as $asset) {
            $view->js[] = $this->asset($asset);
        }
    }
}
