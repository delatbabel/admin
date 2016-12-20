<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

/**
 * Class NoauthViewComposer
 * @package DDPro\Admin\Http\ViewComposers
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
        $view->css[] = $this->bowerAsset('bootstrap/dist/css/bootstrap.min.css');
        $view->css[] = $this->bowerAsset('font-awesome/css/font-awesome.min.css');
        $view->css[] = $this->asset('assets/css/animate.css');
        $view->css[] = $this->asset('assets/css/style.css');
    }
}
