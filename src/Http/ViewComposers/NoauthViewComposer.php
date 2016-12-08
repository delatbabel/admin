<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

class NoauthViewComposer extends ViewComposer
{
    public function compose(View $view)
    {
        $view->css[] = $this->asset('assets/css/bootstrap.min.css');
        $view->css[] = $this->asset('assets/font-awesome/css/font-awesome.css');
        $view->css[] = $this->asset('assets/css/animate.css');
        $view->css[] = $this->asset('assets/css/style.css');
    }
}
