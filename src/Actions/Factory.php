<?php
namespace Delatbabel\Admin\Actions;

use Delatbabel\Admin\Config\ConfigInterface;
use Delatbabel\Admin\Helpers\FunctionHelper;
use Delatbabel\Admin\Validator;
use Log;

/**
 * Class Factory
 *
 * Factory class for Delatbabel admin actions
 *
 * ### Example
 *
 * Bootstrapping -- see AdminServiceProvider
 *
 * <code>
 * $this->app['admin_action_factory'] = $this->app->share(function ($app) {
 *     return new Delatbabel\Admin\Actions\Factory(
 *         $app->make('admin_validator'),
 *         $app->make('itemconfig'),
 *         $app->make('db')
 *     );
 * });
 * </code>
 *
 * @see Action
 */
class Factory
{
    /**
     * The validator instance
     *
     * @var Validator
     */
    protected $validator;

    /**
     * The config instance
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * The actions array
     *
     * @var array
     */
    protected $actions = [];

    /**
     * The array of actions options
     *
     * @var array
     */
    protected $actionsOptions = [];

    /**
     * The action permissions array
     *
     * @var array
     */
    protected $actionPermissions = [];

    /**
     * The global actions array
     *
     * @var array
     */
    protected $globalActions = [];

    /**
     * The array of global actions options
     *
     * @var array
     */
    protected $globalActionsOptions = [];

    /**
     * The action permissions defaults
     *
     * @var array
     */
    protected $actionPermissionsDefaults = [
        'create' => true,
        'delete' => true,
        'update' => true,
        'view'   => true,
    ];

    /**
     * Create a new action Factory instance
     *
     * @param Validator         $validator
     * @param ConfigInterface   $config
     */
    public function __construct(Validator $validator, ConfigInterface $config)
    {
        $this->config    = $config;
        $this->validator = $validator;
    }

    /**
     * Takes the model and an info array of options for the specific action
     *
     * @param string		$name		the key name for this action
     * @param array			$options
     *
     * @return Action
     */
    public function make($name, array $options)
    {
        // check the permission on this item
        $options = $this->parseDefaults($name, $options);

        // now we can instantiate the object
        return $this->getActionObject($options);
    }

    /**
     * Sets up the default values for the $options array
     *
     * @param string		$name		// the key name for this action
     * @param array			$options
     *
     * @return array
     */
    public function parseDefaults($name, $options)
    {
        $model = $this->config->getDataModel();

        // if the name is not a string or the options is not an array at this point, throw an error because we can't do anything with it
        if (! is_string($name) || ! is_array($options)) {
            throw new \InvalidArgumentException("A custom action in your  " . $this->config->getOption('action_name') . " configuration file is invalid");
        }

        // set the action name
        $options['action_name'] = $name;

        // set the permission
        $permission                = $this->validator->arrayGet($options, 'permission', false);
        $options['has_permission'] = FunctionHelper::canCall($permission) ? FunctionHelper::doCall($permission, $model) : true;

        // check if the messages array exists
        $options['messages'] = $this->validator->arrayGet($options, 'messages', []);
        $options['messages'] = is_array($options['messages']) ? $options['messages'] : [];

        return $options;
    }

    /**
     * Gets an Action object
     *
     * @param array		$options
     *
     * @return Action
     */
    public function getActionObject(array $options)
    {
        return new Action($this->validator, $this->config, $options);
    }

    /**
     * Gets an action by name
     *
     * @param string	$name
     * @param bool		$global // if true, search the global actions
     *
     * @return Action
     */
    public function getByName($name, $global = false)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'search for action by name ' . $name);
        $actions = $global ? $this->getGlobalActions() : $this->getActions();

        // loop over the actions to find our culprit
        /** @var Action $action */
        foreach ($actions as $action) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'checking action name ' . $action->getOption('action_name'));

            if ($action->getOption('action_name') === $name) {
                return $action;
            }
        }

        return false;
    }

    /**
     * Gets all actions
     *
     * @param bool	$override
     *
     * @return array of Action objects
     */
    public function getActions($override = false)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'get item actions');

        // make sure we only run this once and then return the cached version
        if (! sizeof($this->actions) || $override) {
            $this->actions = [];

            // loop over the actions to build the list
            if (is_array($this->config->getOption('item_actions'))) {
                foreach ($this->config->getOption('item_actions') as $name => $options) {
                    $this->actions[] = $this->make($name, $options);
                }
            }
        }

        return $this->actions;
    }

    /**
     * Gets all actions as arrays of options
     *
     * @param bool	$override
     *
     * @return array of Action options
     */
    public function getActionsOptions($override = false)
    {
        // make sure we only run this once and then return the cached version
        if (! sizeof($this->actionsOptions) || $override) {
            $this->actionsOptions = [];

            // loop over the actions to build the list
            /** @var Action $action */
            foreach ($this->getActions($override) as $name => $action) {
                $this->actionsOptions[] = $action->getOptions(true);
            }
        }

        return $this->actionsOptions;
    }

    /**
     * Gets all global actions
     *
     * @param bool	$override
     *
     * @return array of Action objects
     */
    public function getGlobalActions($override = false)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'get global actions');

        // make sure we only run this once and then return the cached version
        if (! sizeof($this->globalActions) || $override) {
            $this->globalActions = [];

            // loop over the actions to build the list
            foreach ($this->config->getOption('global_actions') as $name => $options) {
                $this->globalActions[] = $this->make($name, $options);
            }
        }

        return $this->globalActions;
    }

    /**
     * Gets all actions as arrays of options
     *
     * @param bool	$override
     *
     * @return array of Action options
     */
    public function getGlobalActionsOptions($override = false)
    {
        // make sure we only run this once and then return the cached version
        if (! sizeof($this->globalActionsOptions) || $override) {
            $this->globalActionsOptions = [];

            // loop over the global actions to build the list
            /** @var Action $action */
            foreach ($this->getGlobalActions($override) as $name => $action) {
                $this->globalActionsOptions[] = $action->getOptions();
            }
        }

        return $this->globalActionsOptions;
    }

    /**
     * Gets all action permissions
     *
     * @param bool	$override
     *
     * @return array of Action objects
     */
    public function getActionPermissions($override = false)
    {
        // make sure we only run this once and then return the cached version
        if (! sizeof($this->actionPermissions) || $override) {
            $this->actionPermissions = [];
            $model                   = $this->config->getDataModel();
            $options                 = $this->config->getOption('action_permissions');
            $defaults                = $this->actionPermissionsDefaults;

            // merge the user-supplied action permissions into the defaults
            $permissions = array_merge($defaults, $options);

            // loop over the actions to build the list
            foreach ($permissions as $action => $callback) {
                if (FunctionHelper::canCall($callback)) {
                    $this->actionPermissions[$action] = (bool) FunctionHelper::doCall($callback, $model);
                } else {
                    $this->actionPermissions[$action] = (bool) $callback;
                }
            }
        }

        return $this->actionPermissions;
    }
}
