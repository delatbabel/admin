<?php
namespace DDPro\Admin\Http\ViewComposers;

use Illuminate\View\View;

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
        // set up the basic asset arrays
        $view->css = [];

        // Add the package wide JS assets
        $view->js = [
            'jquery'               => $this->bowerAsset('admin-lte/plugins/jQuery/jquery-2.2.3.min.js'),
            'bootstrap'            => $this->bowerAsset('admin-lte/bootstrap/js/bootstrap.min.js'),
            'adminlte-app'         => $this->bowerAsset('admin-lte/dist/js/app.min.js'),
            'date-range-picker1'   => 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js',
            'date-range-picker2'   => $this->bowerAsset('admin-lte/plugins/daterangepicker/daterangepicker.js'),
            'bootstrap-datepicker' => $this->bowerAsset('admin-lte/plugins/datepicker/bootstrap-datepicker.js'),
            'bootstrap-timepicker' => $this->bowerAsset('admin-lte/plugins/timepicker/bootstrap-timepicker.min.js'),
            'datatable'            => $this->bowerAsset('datatables.net/js/jquery.dataTables.min.js'),
            'datatable-bootstrap'  => $this->bowerAsset('datatables.net-bs/js/dataTables.bootstrap.min.js'),
            'datatable-select'     => $this->bowerAsset('datatables.net-select/js/dataTables.select.min.js'),
            'slim-scroll'          => $this->bowerAsset('admin-lte/plugins/slimScroll/jquery.slimscroll.min.js'),
        ];

        // add the non-custom-page css assets
        if (!$view->page && !$view->dashboard) {
            $view->css += [
                'bootstrap'            => $this->bowerAsset('admin-lte/bootstrap/css/bootstrap.min.css'),
                'fontawesome'          => $this->bowerAsset('fontawesome/css/font-awesome.min.css'),
                'ionicons'             => $this->bowerAsset('Ionicons/css/ionicons.min.css'),
                'dateranger-picker'    => $this->bowerAsset('admin-lte/plugins/daterangepicker/daterangepicker.css'),
                'bootstrap-datepicker' => $this->bowerAsset('admin-lte/plugins/datepicker/datepicker3.css'),
                'bootstrap-timepicker' => $this->bowerAsset('admin-lte/plugins/timepicker/bootstrap-timepicker.min.css'),
                'datatable-bs'         => $this->bowerAsset('datatables.net-bs/css/dataTables.bootstrap.min.css'),
                'datatable-select-bs'  => $this->bowerAsset('datatables.net-select-bs/css/select.bootstrap.min.css'),
                'themestyle'           => $this->bowerAsset('admin-lte/dist/css/AdminLTE.css'),
                'themestyle-min'       => $this->bowerAsset('admin-lte/dist/css/AdminLTE.min.css'),
                'skinblue'             => $this->bowerAsset('admin-lte/dist/css/skins/skin-blue.min.css'),
                'icheck'               => $this->bowerAsset('admin-lte/plugins/iCheck/square/blue.css'),
                'select2'              => $this->asset('js/jquery/select2/select2.css'),
                'markitup-style'       => $this->bowerAsset('markitup/markitup/skins/markitup/style.css'),
                'markitup-settings'    => $this->bowerAsset('markitup/markitup/sets/default/style.css'),
            ];
        }

        // add the package-wide css assets
        $view->css += [
            'jquery-colorpicker' => $this->asset('css/jquery.lw-colorpicker.css'),
            'main'               => $this->asset('css/main.css'),
        ];

        // add the non-custom-page js assets
        if (!$view->page && !$view->dashboard) {
            $view->js += [
                'select2'           => $this->asset('js/jquery/select2/select2.js'),
                'ckeditor'          => $this->bowerAsset('admin-lte/plugins/ckeditor/ckeditor.js'),
                'ckeditor-jquery'   => $this->bowerAsset('admin-lte/plugins/ckeditor/adapters/jquery.js'),
                'markdown'          => $this->asset('js/markdown.js'),
                'plupload'          => $this->asset('js/plupload/js/plupload.full.js'),
                'markitup'          => $this->bowerAsset('markitup/markitup/jquery.markitup.js'),
                'markitup-settings' => $this->bowerAsset('markitup/markitup/sets/default/set.js'),
            ];

            // localization js assets
            $locale = config('app.locale');

            if ($locale !== 'en') {
                $view->js += [
                    'plupload-l18n'   => $this->asset('js/plupload/js/i18n/' . $locale . '.js'),
                    'timepicker-l18n' => $this->asset('js/jquery/localization/jquery-ui-timepicker-' . $locale . '.js'),
                    'datepicker-l18n' => $this->asset('js/jquery/i18n/jquery.ui.datepicker-' . $locale . '.js'),
                    'select2-l18n'    => $this->asset('js/jquery/select2/select2_locale_' . $locale . '.js'),
                ];
            }

            // remaining js assets
            // FIXME: These should come from bower
            $view->js += [
                'knockout'                 => $this->bowerAsset('knockout/dist/knockout.js'),
                'knockout-mapping'         => $this->asset('js/knockout/knockout.mapping.js'),
                'knockout-notification'    => $this->asset('js/knockout/KnockoutNotification.knockout.min.js'),
                'knockout-update-data'     => $this->asset('js/knockout/knockout.updateData.js'),
                'knockout-custom-bindings' => $this->asset('js/knockout/custom-bindings.js'),
                'accounting'               => $this->bowerAsset('accountingjs/accounting.min.js'),
                'colorpicker'              => $this->asset('js/jquery/jquery.lw-colorpicker.min.js'),
                'history'                  => $this->asset('js/history/native.history.js'),
                'admin'                    => $this->asset('js/admin.js'),
                'settings'                 => $this->asset('js/settings.js'),
            ];
        }
    }
}
