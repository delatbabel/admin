<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

class NoauthViewComposer extends ViewComposer
{
    use TraitViewComposer;

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        // set up the basic asset arrays
        $view->css = [];

        // Add the package wide JS assets
        $view->js = [
            'jquery'       => $this->bowerAsset('admin-lte/plugins/jQuery/jquery-2.2.3.min.js'),
            'bootstrap'    => $this->bowerAsset('admin-lte/bootstrap/js/bootstrap.min.js'),
            'adminlte-app' => $this->bowerAsset('admin-lte/dist/js/app.min.js'),
        ];

        // add the non-custom-page css assets
        $view->css += [
            'bootstrap'   => $this->bowerAsset('admin-lte/bootstrap/css/bootstrap.min.css'),
            'fontawesome' => $this->bowerAsset('fontawesome/css/font-awesome.min.css'),
            'ionicons'    => $this->bowerAsset('Ionicons/css/ionicons.min.css'),
            'skinblue'    => $this->bowerAsset('admin-lte/dist/css/skins/skin-blue.min.css'),
            'icheck'      => $this->bowerAsset('admin-lte/plugins/iCheck/square/blue.css'),
        ];

        // add the package-wide css assets
        $view->css += [
            'main' => $this->asset('css/main.css'),
        ];
    }
}
