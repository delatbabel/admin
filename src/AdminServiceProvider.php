<?php

namespace DDPro\Admin;

use DDPro\Admin\Actions\Factory as ActionFactory;
use DDPro\Admin\Config\Factory as ConfigFactory;
use DDPro\Admin\DataTable\Columns\Factory as ColumnFactory;
use DDPro\Admin\DataTable\DataTable;
use DDPro\Admin\Fields\Factory as FieldFactory;
use Illuminate\Support\Facades\Validator as LaravelValidator;
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
        // $this->loadViewsFrom(__DIR__ . '/../../views', 'administrator');

        // Should not need this because we will load config from the database.
        /*
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/administrator.php', 'administrator'
        );
        */

        // TBD -- we may keep translations here or we may use gettext
        // $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'administrator');

        // Should not need this because we will load config from the database.
        /*
        $this->publishes([
            __DIR__ . '/../../config/administrator.php' => config_path('administrator.php'),
        ]);
        */

        // Need this because we will publish CSS and JS specific to this package.
        $this->publishes([
            __DIR__ . '/../public' => public_path('packages/ddpro/admin'),
        ], 'public');

        //set the locale
        $this->setLocale();

        // Seems to be useful to keep this here for the time being.
        // event renamed from administrator.ready to admin.ready
        // $this->app['events']->fire('admin.ready');

        // Register other providers required by this provider, which saves the caller
        // from having to register them each individually.
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
        include __DIR__ . '/Helpers/viewComposers.php';
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
}
