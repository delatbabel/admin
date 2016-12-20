<?php

namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

/**
 * Class ViewComposer
 * @package DDPro\Admin\Http\ViewComposers
 */
abstract class ViewComposer
{
    /**
     * Bower-ized asset helper
     *
     * Returns a properly prefixed asset URL for bower-ized assets.
     *
     * @param string $assetName
     * @return string
     */
    protected function bowerAsset($assetName)
    {
        return $this->asset('bower_components/' . $assetName);
    }

    /**
     * Asset helper
     *
     * Returns a properly prefixed asset URL using the Laravel asset() helper.
     *
     * @param string $assetName
     * @return string
     */
    protected function asset($assetName)
    {
        return asset('packages/ddpro/admin/' . $assetName);
    }

    /**
     * Load the CSS and JS files as defined in the config.
     *
     * @param View $view
     * @param      $assets
     */
    protected function generateAssets(View $view, $assets)
    {
        foreach ($assets as $type => $arr) {
            if (isset($arr['bower'])) {
                foreach ($arr['bower'] as $item) {
                    $view->{$type}[] = $this->bowerAsset($item);
                }
            }
            if (isset($arr['base'])) {
                foreach ($arr['base'] as $item) {
                    $view->{$type}[] = $this->asset($item);
                }
            }
        }
    }
}
