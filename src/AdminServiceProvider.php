<?php

namespace DDPro\Admin;

use DDPro\Admin\Actions\Factory as ActionFactory;
use DDPro\Admin\Config\Factory as ConfigFactory;
use DDPro\Admin\Config\Model\Config;
use DDPro\Admin\DataTable\Columns\Factory as ColumnFactory;
use DDPro\Admin\DataTable\DataTable;
use DDPro\Admin\Fields\Factory as FieldFactory;
use DDPro\Admin\Http\Controllers\AdminModelController;
use DDPro\Admin\Http\ViewComposers\MainViewComposer;
use DDPro\Admin\Http\ViewComposers\ModelViewComposer;
use DDPro\Admin\Http\ViewComposers\NoauthViewComposer;
use DDPro\Admin\Http\ViewComposers\SettingViewComposer;
use DDPro\Admin\Http\ViewComposers\SidebarViewComposer;
use Illuminate\Foundation\AliasLoader;
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

        // set the locale
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
        $this->app->register(\Delatbabel\Applog\DebugServiceProvider::class);
        $this->app->register(\Centaur\CentaurServiceProvider::class);
        $this->app->register(\Maatwebsite\Excel\ExcelServiceProvider::class);
        $this->app->register(\Proengsoft\JsValidation\JsValidationServiceProvider::class);

        // register aliases
        $loader = AliasLoader::getInstance();
        $loader->alias('JsValidator', \Proengsoft\JsValidation\Facades\JsValidatorFacade::class);
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
            // get the original validator class so we can set it back after creating our own
            $originalValidator = LaravelValidator::make([], []);
            $originalValidatorClass = get_class($originalValidator);

            // temporarily override the core resolver
            LaravelValidator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($app) {
                $validator = new Validator($translator, $data, $rules, $messages, $customAttributes);
                $validator->setUrlInstance($app->make('url'));
                return $validator;
            });

            // grab our validator instance
            $validator = LaravelValidator::make([], []);

            // set the validator resolver back to the original validator
            LaravelValidator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($originalValidatorClass) {
                return new $originalValidatorClass($translator, $data, $rules, $messages, $customAttributes);
            });

            // return our validator instance
            return $validator;
        });

        // set up the shared instances
        $this->app['admin_config_factory'] = $this->app->share(function ($app) {
            return new ConfigFactory($app->make('admin_validator'), LaravelValidator::make([], []), config('administrator'));
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
        return ['admin_validator', 'admin_config_factory', 'admin_field_factory', 'admin_datatable', 'admin_column_factory',
            'admin_action_factory', 'admin_menu'];
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
        View::composer(config('administrator.model_index_view'), ModelViewComposer::class);

        // admin settings view
        // TODO: This view has not been copied up from staging yet.
        View::composer('administrator::settings', SettingViewComposer::class);

        // header view
        View::composer(['admin.layouts.sidebar'], SidebarViewComposer::class);

        // the main layout view, gets used for all authenticated users. Shows the menu, etc.
        View::composer(['admin.layouts.main'], MainViewComposer::class);

        // the "noauth" layout view, gets used for all non-authenticated users, e.g. the login screens, etc.
        View::composer(['admin.layouts.noauth'], NoauthViewComposer::class);

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
        // Register the additional middleware that we provide
        Route::middleware('post.validate', \DDPro\Admin\Http\Middleware\PostValidate::class);
        Route::middleware('validate.admin', \DDPro\Admin\Http\Middleware\ValidateAdmin::class);
        Route::middleware('validate.model', \DDPro\Admin\Http\Middleware\ValidateModel::class);
        Route::middleware('validate.settings', \DDPro\Admin\Http\Middleware\ValidateSettings::class);

        //
        // Temporary solution for middleware in routes
        // TODO: remove in favor of setting the config for middleware outside of the routes file
        //
        $middleware_array = ['validate.admin'];
        if (is_array(config('administrator.middleware'))) {
            $middleware_array = array_merge(config('administrator.middleware'), $middleware_array);
        }

        //
        // Routes
        //
        Route::group(
            [
                'domain'     => config('administrator.domain'),
                'prefix'     => config('administrator.uri'),
                'middleware' => $middleware_array,
            ], function () {
                // Admin Dashboard
            Route::get('/', [
                'as'   => 'admin_dashboard',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@dashboard',
            ]);

            // File Downloads
            Route::get('file_download', [
                'as'   => 'admin_file_download',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@fileDownload',
            ]);

            // Custom Pages
            Route::get('page/{page}', [
                'as'   => 'admin_page',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@page',
            ]);

            // Switch locales
            Route::get('switch_locale/{locale}', [
                'as'   => 'admin_switch_locale',
                'uses' => 'DDPro\Admin\Http\Controllers\AdminController@switchLocale',
            ]);

                Route::group(['middleware' => ['validate.settings', 'post.validate']], function () {
                    // Settings Pages
                Route::get('settings/{settings}', [
                    'as'   => 'admin_settings',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@settings',
                ]);

                // Display a settings file
                Route::get('settings/{settings}/file', [
                    'as'   => 'admin_settings_display_file',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@displayFile',
                ]);

                // Save Item
                Route::post('settings/{settings}/save', [
                    'as'   => 'admin_settings_save',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@settingsSave',
                ]);

                // Custom Action
                Route::post('settings/{settings}/custom_action', [
                    'as'   => 'admin_settings_custom_action',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@settingsCustomAction',
                ]);

                // Settings file upload
                Route::post('settings/{settings}/{field}/file_upload', [
                    'as'   => 'admin_settings_file_upload',
                    'uses' => 'DDPro\Admin\Http\Controllers\AdminController@fileUpload',
                ]);
                });

            // The route group for all other requests needs to validate admin, model, and add assets
            Route::group(['middleware' => ['validate.model', 'post.validate']], function () {
                // Model Index
                Route::get('{model}', function () {
                    return $this->getController('index', func_get_args());
                })->name('admin_index');

                // New Item
                Route::get('{model}/new', function () {
                    return $this->getController('item', func_get_args());
                })->name('admin_new_item');

                // Update a relationship's items with constraints
                Route::post('{model}/update_options', function () {
                    return $this->getController('updateOptions', func_get_args());
                })->name('admin_update_options');

                // Display an image or file field's image or file
                Route::get('{model}/file', function () {
                    return $this->getController('displayFile', func_get_args());
                })->name('admin_display_file');

                // Updating Rows Per Page
                Route::post('{model}/rows_per_page', function () {
                    return $this->getController('rowsPerPage', func_get_args());
                })->name('admin_rows_per_page');

                // Get results -- new route for DataTable via AJAX POST
                Route::post('{model}/datatable_results', function () {
                    return $this->getController('dataTableResults', func_get_args());
                })->name('admin_get_datatable_results');

                // Custom Model Action
                Route::post('{model}/custom_action', function () {
                    return $this->getController('customModelAction', func_get_args());
                })->name('admin_custom_model_action');

                // Export CSV
                Route::get('{model}/export', function () {
                    return $this->getController('export', func_get_args());
                })->name('admin_export');

                // Get Item
                Route::get('{model}/{id}', function () {
                    return $this->getController('item', func_get_args());
                })->name('admin_get_item');

                // File Uploads
                Route::post('{model}/{field}/file_upload', function () {
                    return $this->getController('fileUpload', func_get_args());
                })->name('admin_file_upload');

                // Save Item
                Route::post('{model}/{id?}/save', function () {
                    return $this->getController('save', func_get_args());
                })->name('admin_save_item');

                // Delete Item
                Route::post('{model}/{id}/delete', function () {
                    return $this->getController('delete', func_get_args());
                })->name('admin_delete_item');

                // Batch Delete Items
                Route::post('{model}/destroy', function () {
                    return $this->getController('destroy', func_get_args());
                })->name('admin_destroy_items');

                // Toggle Activate Items
                Route::post('{model}/toggle_activate', function () {
                    return $this->getController('toggleActivate', func_get_args());
                })->name('admin_toggle_activate_items');

                // Custom Item Action
                Route::post('{model}/{id}/custom_action', function () {
                    return $this->getController('customModelItemAction', func_get_args());
                })->name('admin_custom_model_item_action');

                // Get Custom Item Data
                Route::get('{model}/{id}/custom_data', function () {
                    return $this->getController('customModelItemData', func_get_args());
                })->name('admin_custom_model_item_data');

                // Reorder Item Action
                Route::post('{model}/reorder_item', function () {
                    return $this->getController('reorderItem', func_get_args());
                })->name('admin_reorder_item');
            });
            });

        if (! $this->app->routesAreCached()) {
            require __DIR__ . '/Routes/Auth.php';
        }
    }

    /**
     * Set Controller base on model: AdminModelController or Custom Controller
     * Please refer controller_handler option on Model Config
     *
     * @param       $methodName
     * @param array $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getController($methodName, $params = [])
    {
        /** @var Config $config */
        $config = app('itemconfig');
        /** @var AdminModelController $controller */
        $controller = app($config->getOption('controller_handler'));

        return $controller->callAction($methodName, $params);
    }
}
