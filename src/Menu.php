<?php
namespace DDPro\Admin;

use DDPro\Admin\Config\Factory as ConfigFactory;
use Illuminate\Config\Repository as Config;

/**
 * Class Menu
 *
 * This class produces the site menu from the site configuration.
 *
 * ### Example
 *
 * Building the menu class (done once at the time of bootstrap).
 *
 * <code>
 *   $this->app['admin_menu'] = $this->app->share(function ($app) {
 *       return new Menu($app->make('config'), $app->make('admin_config_factory'));
 *   });
 * </code>
 *
 * Get the site menu and apply it to a view.
 *
 * <code>
 *   $view->menu = app('admin_menu')->getMenu();
 * </code>
 *
 * @see  AdminServiceProvider
 * @see  viewComposers.php
 */
class Menu
{

    /**
     * The config instance
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The config instance
     *
     * @var \DDPro\Admin\Config\Factory
     */
    protected $configFactory;

    /**
     * Create a new Menu instance
     *
     * @param \Illuminate\Config\Repository				$config
     * @param \DDPro\Admin\Config\Factory	$configFactory
     */
    public function __construct(Config $config, ConfigFactory $configFactory)
    {
        $this->config        = $config;
        $this->configFactory = $configFactory;
    }

    /**
     * Gets the menu items indexed by their name with a value of the title
     *
     * @param array		$subMenu (used for recursion)
     *
     * @return array
     */
    public function getMenu($subMenu = null)
    {
        $menu = array();

        if (!$subMenu) {
            $subMenu = $this->config->get('administrator.menu');
        }

        //iterate over the menu to build the return array of valid menu items
        foreach ($subMenu as $key => $item) {
            //if the item is a string, find its config
            if (is_string($item)) {
                //fetch the appropriate config file
                $config = $this->configFactory->make($item);

                //if a config object was returned and if the permission passes, add the item to the menu
                if (is_a($config, 'DDPro\Admin\Config\Config') && $config->getOption('permission')) {
                    $menu[$item] = $config->getOption('title');
                }
                //otherwise if this is a custom page, add it to the menu
                elseif ($config === true) {
                    $menu[$item] = $key;
                }
            }
            //if the item is an array, recursively run this method on it
            elseif (is_array($item)) {
                $menu[$key] = $this->getMenu($item);

                //if the submenu is empty, unset it
                if (empty($menu[$key])) {
                    unset($menu[$key]);
                }
            }
        }

        return $menu;
    }
}
