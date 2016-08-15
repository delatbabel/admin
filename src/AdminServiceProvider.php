<?php

namespace DDPro\Admin;

use DDPro\Admin\Actions\Factory as ActionFactory;
use DDPro\Admin\Config\Factory as ConfigFactory;
use DDPro\Admin\DataTable\Columns\Factory as ColumnFactory;
use DDPro\Admin\DataTable\DataTable;
use DDPro\Admin\Fields\Factory as FieldFactory;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * DDPro Admin Service Provider
 *
 * Service providers are the central place of all Laravel application bootstrapping.
 * Your own application, as well as all of Laravel's core services are bootstrapped
 * via service providers.
 *
 * ### Functionality
 *
 * * All sorts of stuff TBD
 *
 * @see  Illuminate\Support\ServiceProvider
 * @link http://laravel.com/docs/5.1/providers
 */
class AdminServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Should not need this because we will load views from the database.
        // ViewPages doesn't support namespaced views, so we have removed the namespaces.
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'administrator');
        // TODO: Instead of publishing the views, load them up into the database using a seeder.
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views')
        ], 'views');

        // TODO: Load this config from the database.
        /*
        $this->mergeConfigFrom(
            __DIR__ . '/../config/administrator.php', 'administrator'
        );
        */

        // TODO: Load this config from the database.
        $this->publishes([
            __DIR__ . '/../config/administrator.php' => config_path('administrator.php'),
        ], 'config');

        // TBD -- we may keep translations here or we may use gettext
        // TODO: The translations are still in staging at the moment
        $this->loadTranslationsFrom(__DIR__ . '/../staging/lang', 'administrator');

        // Need this because we will publish CSS and JS specific to this package.
        $this->publishes([
            __DIR__ . '/../public' => public_path('packages/ddpro/admin'),
        ], 'public');

        //set the locale
        $this->setLocale();

        // Seems to be useful to keep this here for the time being.
        // event renamed from administrator.ready to admin.ready
        $this->app['events']->fire('admin.ready');

        // Register other providers required by this provider, which saves the caller
        // from having to register them each individually.
        $this->app->register(\Delatbabel\SiteConfig\SiteConfigServiceProvider::class);
        $this->app->register(\Delatbabel\ViewPages\ViewPagesServiceProvider::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //include our view composers, and routes to avoid issues with catch-all routes defined by users
        $this->setViewComposers();
        include __DIR__ . '/Http/Routes/AdminRoutes.php';

        //the admin validator
        // TBD everything from here down
        $this->app['admin_validator'] = $this->app->share(function ($app) {
            //get the original validator class so we can set it back after creating our own
            $originalValidator = LaravelValidator::make(array(), array());
            $originalValidatorClass = get_class($originalValidator);

            //temporarily override the core resolver
            LaravelValidator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($app) {
                $validator = new Validator($translator, $data, $rules, $messages, $customAttributes);
                $validator->setUrlInstance($app->make('url'));
                return $validator;
            });

            //grab our validator instance
            $validator = LaravelValidator::make(array(), array());

            //set the validator resolver back to the original validator
            LaravelValidator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($originalValidatorClass) {
                return new $originalValidatorClass($translator, $data, $rules, $messages, $customAttributes);
            });

            //return our validator instance
            return $validator;
        });

        //set up the shared instances
        $this->app['admin_config_factory'] = $this->app->share(function ($app) {
            return new ConfigFactory($app->make('admin_validator'), LaravelValidator::make(array(), array()), config('administrator'));
        });

        $this->app['admin_field_factory'] = $this->app->share(function ($app) {
            return new FieldFactory($app->make('admin_validator'), $app->make('itemconfig'), $app->make('db'));
        });

        $this->app['admin_datatable'] = $this->app->share(function ($app) {
            $dataTable = new DataTable($app->make('itemconfig'), $app->make('admin_column_factory'), $app->make('admin_field_factory'));
            $dataTable->setRowsPerPage($app->make('session.store'), config('administrator.global_rows_per_page'));

            return $dataTable;
        });

        $this->app['admin_column_factory'] = $this->app->share(function ($app) {
            return new ColumnFactory($app->make('admin_validator'), $app->make('itemconfig'), $app->make('db'));
        });

        $this->app['admin_action_factory'] = $this->app->share(function ($app) {
            return new ActionFactory($app->make('admin_validator'), $app->make('itemconfig'), $app->make('db'));
        });

        $this->app['admin_menu'] = $this->app->share(function ($app) {
            return new Menu($app->make('config'), $app->make('admin_config_factory'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('admin_validator', 'admin_config_factory', 'admin_field_factory', 'admin_datatable', 'admin_column_factory',
            'admin_action_factory', 'admin_menu');
    }

    /**
     * Sets the locale if it exists in the session and also exists in the locales option
     *
     * @return void
     */
    public function setLocale()
    {
        if ($locale = $this->app->session->get('administrator_locale')) {
            $this->app->setLocale($locale);
        }
    }

    protected function setViewComposers()
    {
        // admin index view
        View::composer('adminmodel.index', function ($view) {
            // get a model instance that we'll use for constructing stuff
            $config = app('itemconfig');

            /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
            $fieldFactory  = app('admin_field_factory');

            $columnFactory = app('admin_column_factory');
            $actionFactory = app('admin_action_factory');
            $dataTable = app('admin_datatable');
            $model = $config->getDataModel();
            $baseUrl = route('admin_dashboard');
            $route = parse_url($baseUrl);

            // add the view fields
            $view->config = $config;
            $view->dataTable = $dataTable;
            $view->primaryKey = $model->getKeyName();
            $view->editFields = $fieldFactory->getEditFields();
            $view->arrayFields = $fieldFactory->getEditFieldsArrays();
            $view->dataModel = $fieldFactory->getDataModel();
            $view->columnModel = $columnFactory->getColumnOptions();
            $view->actions = $actionFactory->getActionsOptions();
            $view->globalActions = $actionFactory->getGlobalActionsOptions();
            $view->actionPermissions = $actionFactory->getActionPermissions();
            $view->filters = $fieldFactory->getFiltersArrays();
            $view->rows = $dataTable->getRows(app('db'), $view->filters);
            $view->formWidth = $config->getOption('form_width');
            $view->baseUrl = $baseUrl;
            $view->assetUrl = url('packages/ddpro/admin/');
            $view->route = $route['path'] . '/';
            $view->itemId = isset($view->itemId) ? $view->itemId : null;
        });

        // admin settings view
        // TODO: This view has not been copied up from staging yet.
        View::composer('administrator::settings', function ($view) {
            $config = app('itemconfig');
            /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
            $fieldFactory  = app('admin_field_factory');

            /** @var \DDPro\Admin\Actions\Factory $actionFactory */
            $actionFactory = app('admin_action_factory');

            $baseUrl = route('admin_dashboard');
            $route = parse_url($baseUrl);

            // add the view fields
            $view->config = $config;
            $view->editFields = $fieldFactory->getEditFields();
            $view->arrayFields = $fieldFactory->getEditFieldsArrays();
            $view->actions = $actionFactory->getActionsOptions();
            $view->baseUrl = $baseUrl;
            $view->assetUrl = url('packages/ddpro/admin/');
            $view->route = $route['path'] . '/';
        });

        // header view
        View::composer(array('adminlayouts.sidebar'), function ($view) {
            $view->menu = app('admin_menu')->getMenu();
            $view->settingsPrefix = app('admin_config_factory')->getSettingsPrefix();
            $view->pagePrefix = app('admin_config_factory')->getPagePrefix();
            $view->configType = app()->bound('itemconfig') ? app('itemconfig')->getType() : false;
        });

        // the layout view
        View::composer(array('adminlayouts.main'), function ($view) {
            // set up the basic asset arrays
            $view->css = array();
            $view->js = array(
                'jquery'       => asset('packages/ddpro/admin/js/jquery/jquery-1.8.2.min.js'),
                'jquery-ui'    => asset('packages/ddpro/admin/js/jquery/jquery-ui-1.10.3.custom.min.js'),
                'customscroll' => asset('packages/ddpro/admin/js/jquery/customscroll/jquery.customscroll.js'),
            );

            // add the non-custom-page css assets
            if (!$view->page && !$view->dashboard) {
                $view->css += array(
                    'jquery-ui'            => asset('packages/ddpro/admin/css/ui/jquery-ui-1.9.1.custom.min.css'),
                    'jquery-ui-timepicker' => asset('packages/ddpro/admin/css/ui/jquery.ui.timepicker.css'),
                    'select2'              => asset('packages/ddpro/admin/js/jquery/select2/select2.css'),
                    'jquery-colorpicker'   => asset('packages/ddpro/admin/css/jquery.lw-colorpicker.css'),
                );
            }

            // add the package-wide css assets
            $view->css += array(
                'customscroll' => asset('packages/ddpro/admin/js/jquery/customscroll/customscroll.css'),
                'main'         => asset('packages/ddpro/admin/css/main.css'),
            );

            // add the non-custom-page js assets
            if (!$view->page && !$view->dashboard) {
                $view->js += array(
                    'select2'              => asset('packages/ddpro/admin/js/jquery/select2/select2.js'),
                    'jquery-ui-timepicker' => asset('packages/ddpro/admin/js/jquery/jquery-ui-timepicker-addon.js'),
                    'ckeditor'             => asset('packages/ddpro/admin/js/ckeditor/ckeditor.js'),
                    'ckeditor-jquery'      => asset('packages/ddpro/admin/js/ckeditor/adapters/jquery.js'),
                    'markdown'             => asset('packages/ddpro/admin/js/markdown.js'),
                    'plupload'             => asset('packages/ddpro/admin/js/plupload/js/plupload.full.js'),
                );

                // localization js assets
                $locale = config('app.locale');

                if ($locale !== 'en') {
                    $view->js += array(
                        'plupload-l18n'   => asset('packages/ddpro/admin/js/plupload/js/i18n/' . $locale . '.js'),
                        'timepicker-l18n' => asset('packages/ddpro/admin/js/jquery/localization/jquery-ui-timepicker-' . $locale . '.js'),
                        'datepicker-l18n' => asset('packages/ddpro/admin/js/jquery/i18n/jquery.ui.datepicker-' . $locale . '.js'),
                        'select2-l18n'    => asset('packages/ddpro/admin/js/jquery/select2/select2_locale_' . $locale . '.js'),
                    );
                }

                // remaining js assets
                $view->js += array(
                    'knockout'                 => asset('packages/ddpro/admin/js/knockout/knockout-2.2.0.js'),
                    'knockout-mapping'         => asset('packages/ddpro/admin/js/knockout/knockout.mapping.js'),
                    'knockout-notification'    => asset('packages/ddpro/admin/js/knockout/KnockoutNotification.knockout.min.js'),
                    'knockout-update-data'     => asset('packages/ddpro/admin/js/knockout/knockout.updateData.js'),
                    'knockout-custom-bindings' => asset('packages/ddpro/admin/js/knockout/custom-bindings.js'),
                    'accounting'               => asset('packages/ddpro/admin/js/accounting.js'),
                    'colorpicker'              => asset('packages/ddpro/admin/js/jquery/jquery.lw-colorpicker.min.js'),
                    'history'                  => asset('packages/ddpro/admin/js/history/native.history.js'),
                    'admin'                    => asset('packages/ddpro/admin/js/admin.js'),
                    'settings'                 => asset('packages/ddpro/admin/js/settings.js'),
                );
            }

            $view->js += array('page' => asset('packages/ddpro/admin/js/page.js'));
        });

    }
}
