<?php
namespace DDPro\Admin\Actions;

use DDPro\Admin\Config\ConfigInterface;
use DDPro\Admin\Helpers\FunctionHelper;
use DDPro\Admin\Validator;

/**
 * Class Action
 *
 * This class manages all of the actions taken on a database record, e.g.
 * view, edit, delete, etc.
 *
 * You can define custom actions in your model or settings config files if
 * you want to provide the administrative user buttons to perform custom code.
 * You can modify an Eloquent model, or on a settings page you can give a user
 * a button to clear the site cache or backup the database.
 *
 * ### Example
 *
 * Creating a new action object (done from the factory)
 *
 * ```php
 * return new Action($this->validator, $this->config, $options);
 * ```
 *
 * @link https://github.com/ddpro/admin/blob/master/docs/actions.md
 * @link https://github.com/ddpro/admin/blob/master/docs/model-configuration.md#custom-actions
 */
class Action
{

    /**
     * The validator instance
     *
     * @var \DDPro\Admin\Validator
     */
    protected $validator;

    /**
     * The config instance
     *
     * @var \DDPro\Admin\Config\ConfigInterface
     */
    protected $config;

    /**
     * The user supplied options array
     *
     * @var array
     */
    protected $suppliedOptions = [];

    /**
     * The options array
     *
     * @var array
     */
    protected $options = [];

    /**
     * The default configuration options
     *
     * @var array
     */
    protected $defaults = [
        'title'          => 'Custom Action',
        'has_permission' => true,
        'confirmation'   => false,
        'messages'       => [
            'active'  => 'Just a moment...',
            'success' => 'Success!',
            'error'   => 'There was an error performing this action',
        ],
    ];

    /**
     * The base rules that all fields need to pass
     *
     * @var array
     */
    protected $rules = [
        'title'        => 'string_or_callable',
        'confirmation' => 'string_or_callable',
        'messages'     => 'array|array_with_all_or_none:active,success,error',
    ];

    /**
     * Create a new action Factory instance
     *
     * @param \DDPro\Admin\Validator 				$validator
     * @param \DDPro\Admin\Config\ConfigInterface	$config
     * @param array												$options
     */
    public function __construct(Validator $validator, ConfigInterface $config, array $options)
    {
        $this->config          = $config;
        $this->validator       = $validator;
        $this->suppliedOptions = $options;
    }

    /**
     * Validates the supplied options
     *
     * @return void
     */
    public function validateOptions()
    {
        // override the config
        $this->validator->override($this->suppliedOptions, $this->rules);

        // if the validator failed, throw an exception
        if ($this->validator->fails()) {
            throw new \InvalidArgumentException("There are problems with your '" . $this->suppliedOptions['action_name'] . "' action in the " .
                                    $this->config->getOption('name') . " model: " .    implode('. ', $this->validator->messages()->all()));
        }
    }

    /**
     * Builds the necessary fields on the object
     *
     * @return void
     */
    public function build()
    {
        $options = $this->suppliedOptions;

        // build the string or callable values for title and confirmation
        $this->buildStringOrCallable($options, ['confirmation', 'title']);

        // build the string or callable values for the messages
        $messages = $this->validator->arrayGet($options, 'messages', []);
        $this->buildStringOrCallable($messages, ['active', 'success', 'error']);
        $options['messages'] = $messages;

        // override the supplied options
        $this->suppliedOptions = $options;
    }

    /**
     * Sets up the values of all the options that can be either strings or closures
     *
     * @param array		$options	// the passed-by-reference array on which to do the transformation
     * @param array		$keys		// the keys to check
     *
     * @return void
     */
    public function buildStringOrCallable(array &$options, array $keys)
    {
        $model = $this->config->getDataModel();

        // iterate over the keys
        foreach ($keys as $key) {
            // check if the key's value was supplied
            $suppliedValue = $this->validator->arrayGet($options, $key);

            // if it's a string, simply set it
            if (is_string($suppliedValue)) {
                $options[$key] = $suppliedValue;
            }
            // if it's callable pass it the current model and run it
            elseif (FunctionHelper::canCall($suppliedValue)) {
                $options[$key] = FunctionHelper::doCall($suppliedValue, $model);
            }
        }
    }

    /**
     * Performs the callback of the action and returns its result
     *
     * @param mixed		$data
     *
     * @return array
     */
    public function perform(&$data)
    {
        /** @var Callable $action */
        $action = $this->getOption('action');
        return $action($data);
    }

    /**
     * Gets all user options
     *
     * @param bool	$override
     *
     * @return array
     */
    public function getOptions($override = false)
    {
        // if override is true, unset the current options
        $this->options = $override ? [] : $this->options;

        // make sure the supplied options have been merged with the defaults
        if (empty($this->options) || (count($this->options) == 0)) {
            // validate the options and build them
            $this->validateOptions();
            $this->build();
            $this->options = array_merge($this->defaults, $this->suppliedOptions);
        }

        return $this->options;
    }

    /**
     * Gets a field's option
     *
     * @param string 	$key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        $options = $this->getOptions();

        if (! array_key_exists($key, $options)) {
            throw new \InvalidArgumentException("An invalid option was searched for in the '" . $options['action_name'] . "' action");
        }

        return $options[$key];
    }
}
