<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

class MainViewComposer extends ViewComposer
{
    public function compose(View $view)
    {
        /* Mainly CSS */
        $view->css[] = $this->asset('assets/css/bootstrap.min.css');
        $view->css[] = $this->asset('assets/font-awesome/css/font-awesome.css');
        $view->css[] = $this->asset('assets/css/style.css');
        $view->css[] = $this->asset('assets/css/animate.css');

        /* Custom and plugin CSS */
        $view->css[] = $this->asset('assets/css/plugins/dataTables/datatables.min.css');
        $view->css[] = $this->asset('assets/css/plugins/select2/select2.min.css');
        $view->css[] = $this->asset('assets/css/plugins/colorpicker/bootstrap-colorpicker.min.css');
        $view->css[] = $this->asset('assets/css/plugins/chosen/bootstrap-chosen.css');
        $view->css[] = $this->asset('assets/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css');
        $view->css[] = $this->bowerAsset('markitup/markitup/skins/markitup/style.css');
        $view->css[] = $this->bowerAsset('markitup/markitup/sets/default/style.css');
        $view->css[] = $this->bowerAsset('jquery-ui/themes/flick/jquery-ui.min.css');
        $view->css[] = $this->bowerAsset('jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css');

        /* Mainly scripts */
        $view->js[] = $this->asset('assets/js/jquery-2.1.1.js');
        $view->js[] = $this->asset('assets/js/bootstrap.min.js');
        $view->js[] = $this->asset('assets/js/plugins/metisMenu/jquery.metisMenu.js');
        $view->js[] = $this->asset('assets/js/plugins/slimscroll/jquery.slimscroll.min.js');

        /* Custom and plugin javascript */
        $view->js[] = $this->asset('assets/js/inspinia.js');
        $view->js[] = $this->asset('assets/js/plugins/pace/pace.min.js');
        $view->js[] = $this->asset('assets/js/plugins/dataTables/datatables.min.js');
        $view->js[] = $this->asset('assets/js/plugins/select2/select2.full.min.js');
        $view->js[] = $this->asset('assets/js/plugins/colorpicker/bootstrap-colorpicker.min.js');
        $view->js[] = $this->asset('assets/js/plugins/chosen/chosen.jquery.js');
        $view->js[] = $this->bowerAsset('markitup/markitup/jquery.markitup.js');
        $view->js[] = $this->bowerAsset('markitup/markitup/sets/default/set.js');
        $view->js[] = $this->bowerAsset('jquery-ui/jquery-ui.min.js');
        $view->js[] = $this->bowerAsset('jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js');
    }
}
