<?php

namespace DDPro\Admin;

use DDPro\Admin\Actions\Factory as ActionFactory;
use DDPro\Admin\Config\Factory as ConfigFactory;
use DDPro\Admin\DataTable\Columns\Factory as ColumnFactory;
use DDPro\Admin\DataTable\DataTable;
use DDPro\Admin\Fields\Factory as FieldFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
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
 * See the boot() and register() functions.
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
     * This method is called after all other service providers have been registered,
     * meaning you have access to all other services that have been registered by the
     * framework.
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

        // Include our view composers,  Do this in the boot method because we use config
        // variables which may not be available in the register method.
        $this->setViewComposers();

        // Include the routes.  Ideally this should be done early to avoid issues with catch
        // all routes defined by other packages and applications.
        $this->publishRoutes();

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
     * Within the register method, you should only bind things into the service container.
     * You should never attempt to register any event listeners, routes, or any other piece
     * of functionality within the register method. Otherwise, you may accidentally use
     * a service that is provided by a service provider which has not loaded yet.
     *
     * @return void
     */
    public function register()
    {
        // the admin validator
        $this->app['admin_validator'] = $this->app->share(function ($app) {
            //get the original validator class so we can set it back after creating our own
            $originalValidator = LaravelValidator::make(array(), array());
            $originalValidatorClass = get_class($originalValidator);

            // temporarily override the core resolver
            LaravelValidator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($app) {
                $validator = new Validator($translator, $data, $rules, $messages, $customAttributes);
                $validator->setUrlInstance($app->make('url'));
                return $validator;
            });

            // grab our validator instance
            $validator = LaravelValidator::make(array(), array());

            //set the validator resolver back to the original validator
            LaravelValidator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($originalValidatorClass) {
                return new $originalValidatorClass($translator, $data, $rules, $messages, $customAttributes);
            });

            // return our validator instance
            return $validator;
        });

        // set up the shared instances
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
     * Set the View Composers
     *
     * View composers are callbacks or class methods that are called when a view is rendered. If you
     * have data that you want to be bound to a view each time that view is rendered, a view composer
     * can help you organize that logic into a single location.
     *
     * This binds all of the data to the views within Admin.
     *
     * See: https://laravel.com/docs/5.1/views#view-composers
     *
     * @return void
     */
    protected function setViewComposers()
    {
        // admin index view
        View::composer(config('administrator.model_index_view'), function ($view) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'model index view');

            // get a model instance that we'll use for constructing stuff
            // The itemconfig singleton is built in the ValidateModel middleware and
            // will be an instance of \DDPro\Admin\Config\Model\Config
            /** @var \DDPro\Admin\Config\Model\Config $config */
            $config = app('itemconfig');

            /** @var FieldFactory $fieldFactory */
            $fieldFactory  = app('admin_field_factory');

            /** @var ColumnFactory $columnFactory */
            $columnFactory = app('admin_column_factory');

            /** @var ActionFactory $actionFactory */
            $actionFactory = app('admin_action_factory');

            /** @var DataTable $dataTable */
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
            $view->js=array(
                'jquery'               => $this->bowerAsset('admin-lte/plugins/jQuery/jquery-2.2.3.min.js'),
                'bootstrap'            => $this->bowerAsset('admin-lte/bootstrap/js/bootstrap.min.js'),
                'date-range-picker1'   => 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js',
                'date-range-picker2'   => $this->bowerAsset('admin-lte/plugins/daterangepicker/daterangepicker.js'),
                'bootstrap-datepicker' => $this->bowerAsset('admin-lte/plugins/datepicker/bootstrap-datepicker.js'),
                'bootstrap-timepicker' => $this->bowerAsset('admin-lte/plugins/timepicker/bootstrap-timepicker.min.js'),
                'datatable'            => $this->bowerAsset('admin-lte/plugins/datatables/jquery.dataTables.min.js'),
                'datatable-bootstrap'  => $this->bowerAsset('admin-lte/plugins/datatables/dataTables.bootstrap.min.js'),
                'slim-scroll'          => $this->bowerAsset('admin-lte/plugins/slimScroll/jquery.slimscroll.min.js'),
            );
            $view->js += array(
                'customscroll' => $this->asset('js/jquery/customscroll/jquery.customscroll.js'),
            );

            // add the adminlte-page css assets
            if (!$view->page && !$view->dashboard) {
                $view->css += array(
                    'bootstrap'           => $this->bowerAsset('admin-lte/bootstrap/css/bootstrap.min.css'),
                    'fontawesome'         => $this->bowerAsset('fontawesome/css/font-awesome.min.css'),
                    'ionicons'            => $this->bowerAsset('Ionicons/css/ionicons.min.css'),
                    'dateranger-picker'   => $this->bowerAsset('admin-lte/plugins/daterangepicker/daterangepicker.css'),
                    'bootstrap-datepicker'=> $this->bowerAsset('admin-lte/plugins/datepicker/datepicker3.css'),
                    'bootstrap-timepicker'=> $this->bowerAsset('admin-lte/plugins/timepicker/bootstrap-timepicker.min.css'),
                    'datatable'           => $this->bowerAsset('admin-lte/plugins/datatables/dataTables.bootstrap.css'),
                    'themestyle'          => $this->bowerAsset('admin-lte/dist/css/AdminLTE.css'),
                    'skinblue'            => $this->bowerAsset('admin-lte/dist/css/skins/skin-blue.min.css'),
                    'icheck'              => $this->bowerAsset('admin-lte/plugins/iCheck/square/blue.css'),
                );
            }

            // add the non-custom-page css assets
            if (!$view->page && !$view->dashboard) {
                $view->css += array(
                     'select2'              => $this->asset('js/jquery/select2/select2.css')
                );
            }

            // add the package-wide css assets
            $view->css += array(
                'customscroll' => $this->asset('js/jquery/customscroll/customscroll.css'),
                'main'         => $this->asset('css/main.css'),
            );

            // add the non-custom-page js assets
            if (!$view->page && !$view->dashboard) {
                $view->js += array(
                    'select2'              => $this->asset('js/jquery/select2/select2.js'),
                    'ckeditor'             => $this->asset('js/ckeditor/ckeditor.js'),
                    'ckeditor-jquery'      => $this->asset('js/ckeditor/adapters/jquery.js'),
                    'markdown'             => $this->asset('js/markdown.js'),
                    'plupload'             => $this->asset('js/plupload/js/plupload.full.js'),
                );

                // localization js assets
                $locale = config('app.locale');

                if ($locale !== 'en') {
                    $view->js += array(
                        'plupload-l18n'   => $this->asset('js/plupload/js/i18n/' . $locale . '.js'),
                        'timepicker-l18n' => $this->asset('js/jquery/localization/jquery-ui-timepicker-' . $locale . '.js'),
                        'datepicker-l18n' => $this->asset('js/jquery/i18n/jquery.ui.datepicker-' . $locale . '.js'),
                        'select2-l18n'    => $this->asset('js/jquery/select2/select2_locale_' . $locale . '.js'),
                    );
                }

                // remaining js assets
                $view->js += array(
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
                );

            }

            $view->js += array('page' => $this->asset('js/page.js'));
        });

        // An example of bower-izing one of the assets
        //
        // (1) bower install accountingjs --save
        //
        // (2) Change this:
        //
        // $this->asset('js/accounting.js')
        //
        // To this:
        // $this->bowerAsset('accountingjs/accounting.min.js')
        //
        // (3) Remove the old asset file public/js/accounting.js
    }

    /**
     * Publish routes
     */
    protected function publishRoutes()
    {
        //
        // Temporary solution for middleware in routes
        // TODO: remove in favor of setting the config for middleware outside of the routes file
        //
        $middleware_array = array('DDPro\Admin\Http\Middleware\ValidateAdmin');
        if (is_array(config('administrator.middleware'))) {
            $middleware_array = array_merge(config('administrator.middleware'), $middleware_array);
        }

        //
        // Routes
        //
        Route::group(
            array(
                'domain'     => config('administrator.domain'),
                'prefix'     => config('administrator.uri'),
                'middleware' => $middleware_array
            ), function () {
            //Admin Dashboard
            Route::get('/', array(
                'as'   => 'admin_dashboard',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@dashboard',
            ));

            //File Downloads
            Route::get('file_download', array(
                'as'   => 'admin_file_download',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@fileDownload'
            ));

            //Custom Pages
            Route::get('page/{page}', array(
                'as'   => 'admin_page',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@page'
            ));

            Route::group(
                array(
                    'middleware' => [
                        'DDPro\Admin\Http\Middleware\ValidateSettings',
                        'DDPro\Admin\Http\Middleware\PostValidate']
                ), function () {
                //Settings Pages
                Route::get('settings/{settings}', array(
                    'as'   => 'admin_settings',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@settings'
                ));

                //Display a settings file
                Route::get('settings/{settings}/file', array(
                    'as'   => 'admin_settings_display_file',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@displayFile'
                ));

                //Save Item
                Route::post('settings/{settings}/save', array(
                    'as'   => 'admin_settings_save',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@settingsSave'
                ));

                //Custom Action
                Route::post('settings/{settings}/custom_action', array(
                    'as'   => 'admin_settings_custom_action',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@settingsCustomAction'
                ));

                //Settings file upload
                Route::post('settings/{settings}/{field}/file_upload', array(
                    'as'   => 'admin_settings_file_upload',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@fileUpload'
                ));
            });

            //Switch locales
            Route::get('switch_locale/{locale}', array(
                'as'   => 'admin_switch_locale',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@switchLocale'
            ));

            //The route group for all other requests needs to validate admin, model, and add assets
            Route::group(
                array(
                    'middleware' => [
                        'DDPro\Admin\Http\Middleware\ValidateModel',
                        'DDPro\Admin\Http\Middleware\PostValidate']
                ), function () {
                //Model Index
                Route::get('{model}', array(
                    'as'   => 'admin_index',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@index'
                ));

                //New Item
                Route::get('{model}/new', array(
                    'as'   => 'admin_new_item',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@item'
                ));

                //Update a relationship's items with constraints
                Route::post('{model}/update_options', array(
                    'as'   => 'admin_update_options',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@updateOptions'
                ));

                //Display an image or file field's image or file
                Route::get('{model}/file', array(
                    'as'   => 'admin_display_file',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@displayFile'
                ));

                //Updating Rows Per Page
                Route::post('{model}/rows_per_page', array(
                    'as'   => 'admin_rows_per_page',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@rowsPerPage'
                ));

                //Get results
                Route::post('{model}/results', array(
                    'as'   => 'admin_get_results',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@results'
                ));

                //Custom Model Action
                Route::post('{model}/custom_action', array(
                    'as'   => 'admin_custom_model_action',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@customModelAction'
                ));

                //Get Item
                Route::get('{model}/{id}', array(
                    'as'   => 'admin_get_item',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@item'
                ));

                //File Uploads
                Route::post('{model}/{field}/file_upload', array(
                    'as'   => 'admin_file_upload',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@fileUpload'
                ));

                //Save Item
                Route::post('{model}/{id?}/save', array(
                    'as'   => 'admin_save_item',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@save'
                ));

                //Delete Item
                Route::post('{model}/{id}/delete', array(
                    'as'   => 'admin_delete_item',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@delete'
                ));

                //Custom Item Action
                Route::post('{model}/{id}/custom_action', array(
                    'as'   => 'admin_custom_model_item_action',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@customModelItemAction'
                ));
            });
        });
    }
}
