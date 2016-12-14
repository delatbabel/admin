<?php

return [

    /**
     * Package URI
     *
     * @type string
     */
    'uri' => 'admin',

    /**
     *  Domain for routing.
     *
     *  @type string
     */
    'domain' => '',

    /**
     *  Middleware for admin routing.
     *
     * By default the standard Admin middleware is merged into to this array.  The standard
     * Admin middleware is
     *
     * * DDPro\Admin\Http\Middleware\ValidateAdmin
     * * DDPro\Admin\Http\Middleware\ValidateSettings (settings only)
     * * DDPro\Admin\Http\Middleware\ValidateModel (model only)
     * * DDPro\Admin\Http\Middleware\PostValidate
     *
     *  @type array
     */
    'middleware' => [],

    /**
     * Page title
     *
     * @type string
     */
    'title' => 'Admin',

    /**
     * The company name, that will appear in the copyright message in the page footer.
     *
     * @type string
     */
    'company_name'  => 'Babel Consulting',

    /**
     * The company website URL, that will appear in the copyright message in the page footer.
     *
     * @type string
     */
    'company_url'  => 'https://www.babel.com.au/',

    /**
     * The message that will appear in the page footer.
     *
     * @type string
     */
    'footer_message'  => 'Example Application using DDPro Classes',

    /**
     * The path to your model config directory
     *
     * @type string
     */
    'model_config_path' => config_path('administrator'),

    /**
     * The path to your settings config directory
     *
     * @type string
     */
    'settings_config_path' => config_path('administrator/settings'),

    /**
     * The menu structure of the site. For models, you should either supply the name of a model config file or an array of names of model config
     * files. The same applies to settings config files, except you must prepend 'settings.' to the settings config file name. You can also add
     * custom pages by prepending a view path with 'page.'. By providing an array of names, you can group certain models or settings pages
     * together. Each name needs to either have a config file in your model config path, settings config path with the same name, or a path to a
     * fully-qualified Laravel view. So 'users' would require a 'users.php' file in your model config path, 'settings.site' would require a
     * 'site.php' file in your settings config path, and 'page.foo.test' would require a 'test.php' or 'test.blade.php' file in a 'foo' directory
     * inside your view directory.
     *
     * @type array
     *
     *  array(
     *      'E-Commerce' => array('collections', 'products', 'product_images', 'orders'),
     *      'homepage_sliders',
     *      'users',
     *      'roles',
     *      'colors',
     *      'Settings' => array('settings.site', 'settings.ecommerce', 'settings.social'),
     *      'Analytics' => array('E-Commerce' => 'page.ecommerce.analytics'),
     *  )
     */
    'menu' => [],

    /**
     * The permission option is the highest-level authentication check that lets you define a closure that should return true if the current user
     * is allowed to view the admin section. Any "falsey" response will send the user back to the 'login_path' defined below.
     *
     * TODO: We want to move the config out of the config files and into the database but we can't do that while there is
     * a closure in the config file. Replace this with something else (e.g. a class/static function name, etc).
     *
     * @type closure
     */
    'permission' => function () {
        return Auth::check();
    },

    /**
     * This determines if you will have a dashboard (whose view you provide in the dashboard_view option) or a non-dashboard home
     * page (whose menu item you provide in the home_page option)
     *
     * @type bool
     */
    'use_dashboard' => true,

    /**
     * If you want to create a dashboard view, provide the view string here.
     *
     * @type string
     */
    'dashboard_view' => 'admindashboard.admin',

    /**
     * The name of the model index view
     *
     * @type string
     */
    'model_index_view' => 'adminmodel.index',

    /**
     * The menu item that should be used as the default landing page of the administrative section
     *
     * @type string
     */
    'home_page' => '',

    /**
     * The route to which the user will be taken when they click the "back to site" button
     *
     * @type string
     */
    'back_to_site_path' => '/',

    /**
     * The login path is the path where Administrator will send the user if they fail a permission check
     *
     * @type string
     */
    'login_path' => 'auth/login',

    /**
     * The logout path is the path where Administrator will send the user when they click the logout link
     *
     * @type string
     */
    'logout_path' => 'auth/logout',

    /**
     * This is the key of the return path that is sent with the redirection to your login_action. Session::get('redirect') will hold the return URL.
     *
     * @type string
     */
    'login_redirect_key' => 'redirect',

    /**
     * Global default rows per page
     *
     * @type int
     */
    'global_rows_per_page' => 20,

    /**
     * An array of available locale strings. This determines which locales are available in the languages menu at the top right of the Administrator
     * interface.
     *
     * @type array
     */
    'locales' => [],

    'assets' => [
        'css'   => [
            'base' => [
                // Main CSS
                'assets/css/bootstrap.min.css',
                'assets/font-awesome/css/font-awesome.css',
                'assets/css/style.css',
                'assets/css/custom.css',
                'assets/css/animate.css',

                // Custom and plugin CSS
                'assets/css/plugins/dataTables/datatables.min.css',
                'assets/css/plugins/select2/select2.min.css',
                'assets/css/plugins/colorpicker/bootstrap-colorpicker.min.css',
                'assets/css/plugins/chosen/bootstrap-chosen.css',
                'assets/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
            ],
            'bower' => [
                'markitup/markitup/skins/markitup/style.css',
                'markitup/markitup/sets/default/style.css',
                'markitup/markitup/sets/html/style.css',
                'markitup/markitup/sets/markdown/style.css',
                'jquery-ui/themes/flick/jquery-ui.min.css',
                'jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css',
                'jsoneditor/dist/jsoneditor.min.css',
            ],
        ],
        'js'    => [
            'base' => [
                // Main scripts
                'assets/js/jquery-2.1.1.js',
                'assets/js/bootstrap.min.js',
                'assets/js/plugins/metisMenu/jquery.metisMenu.js',
                'assets/js/plugins/slimscroll/jquery.slimscroll.min.js',

                // Custom and plugin javascript
                'assets/js/inspinia.js',
                'assets/js/plugins/pace/pace.min.js',
                'assets/js/plugins/dataTables/datatables.min.js',
                'assets/js/plugins/select2/select2.full.min.js',
                'assets/js/plugins/colorpicker/bootstrap-colorpicker.min.js',
                'assets/js/plugins/chosen/chosen.jquery.js',
            ],
            'bower' => [
                'markitup/markitup/jquery.markitup.js',
                'markitup/markitup/sets/html/set.js',
                'markitup/markitup/sets/markdown/set.js',
                'jquery-ui/jquery-ui.min.js',
                'jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js',
                'jsoneditor/dist/jsoneditor.min.js',
            ],
        ],
    ],
];
