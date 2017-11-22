<?php
namespace Delatbabel\Admin\DataTable\Columns;

use Delatbabel\Admin\Config\ConfigInterface;
use Delatbabel\Admin\DataTable\Columns\Relationships\Relationship;
use Delatbabel\Admin\Helpers\FunctionHelper;
use Delatbabel\Admin\Validator;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Column
 *
 * The DataTable manages the table view in the model index page.  Each column in the
 * DataTable is represented by a Column object.
 *
 * This is the class that manages all basic (non-relationship) columns within the
 * Admin DataTable class.
 *
 * The Column class helps us construct columns from models. It can be used to
 * derive column information from a model, or it can be instantiated to hold
 * information about any given column.
 *
 * ### Example
 *
 * #### Construction of the Column Object
 *
 * This happens in the factory during the `make()` call:
 *
 * ```php
 * $column = new Column($this->validator, $this->config, $this->db, $options);
 * ```
 *
 * #### Rendering the Column Output
 *
 * <code>
 * $rendered = $column->renderOutput($attributeValue, $item)
 * </code>
 *
 * @see Delatbabel\Admin\DataTable\Columns\Factory
 * @see Delatbabel\Admin\DataTable\DataTable
 * @link https://github.com/ddpro/admin/blob/master/docs/columns.md
 */
class Column
{

    /**
     * The validator instance
     *
     * @var \Delatbabel\Admin\Validator
     */
    protected $validator;

    /**
     * The config instance
     *
     * @var \Delatbabel\Admin\Config\ConfigInterface
     */
    protected $config;

    /**
     * The database instance
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * The options array
     *
     * @var array
     */
    protected $options;

    /**
     * The originally-supplied options array
     *
     * @var array
     */
    protected $suppliedOptions;

    /**
     * The default configuration options
     *
     * @var array
     */
    protected $baseDefaults = [
        'relationship'    => false,
        'sortable'        => true,
        'select'          => false,
        'output'          => '(:value)',
        'sort_field'      => null,
        'nested'          => [],
        'is_related'      => false,
        'is_computed'     => false,
        'is_included'     => false,
        'external'        => false,
        'belongs_to_many' => false,
        'visible'         => true,
        'type'            => null,
    ];

    /**
     * The specific defaults for subclasses to override
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * The base rules that all fields need to pass
     *
     * @var array
     */
    protected $baseRules = [
        'column_name'  => 'required|string',
        'title'        => 'string',
        'relationship' => 'string',
        'select'       => 'required_with:relationship|string'
    ];

    /**
     * The specific rules for subclasses to override
     *
     * @var array
     */
    protected $rules = [];

    /**
     * The immediate relationship object for this column
     *
     * @var Relationship
     */
    protected $relationshipObject = null;

    /**
     * The table prefix
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Create a new action Factory instance
     *
     * @param \Delatbabel\Admin\Validator 				$validator
     * @param \Delatbabel\Admin\Config\ConfigInterface	$config
     * @param \Illuminate\Database\DatabaseManager 	$db
     * @param array									$options
     */
    public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
    {
        $this->config          = $config;
        $this->validator       = $validator;
        $this->db              = $db;
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
        $this->validator->override($this->suppliedOptions, $this->getRules());

        // if the validator failed, throw an exception
        if ($this->validator->fails()) {
            throw new \InvalidArgumentException("There are problems with your '" . $this->suppliedOptions['column_name'] . "' column in the " .
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
        $model             = $this->config->getDataModel();
        $options           = $this->suppliedOptions;
        $this->tablePrefix = $this->db->getTablePrefix();

        // set some options-based defaults
        $options['title']      = $this->validator->arrayGet($options, 'title', $options['column_name']);
        $options['sort_field'] = $this->validator->arrayGet($options, 'sort_field', $options['column_name']);

        // if the supplied item is an accessor, make this unsortable for the moment
        if (method_exists($model, camel_case('get_' . $options['column_name'] . '_attribute')) && $options['column_name'] === $options['sort_field']) {
            $options['sortable'] = false;
        }

        // however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
        if ($select = $this->validator->arrayGet($options, 'select')) {
            $options['select'] = str_replace('(:table)', $this->tablePrefix . $model->getTable(), $select);
        }

        // now we do some final organization to categorize these columns (useful later in the sorting)
        if (method_exists($model, camel_case('get_' . $options['column_name'] . '_attribute')) || $select) {
            $options['is_computed'] = true;
        } else {
            $options['is_included'] = true;
        }

        // run the visible property closure if supplied
        $visible = $this->validator->arrayGet($options, 'visible');

        if (FunctionHelper::canCall($visible)) {
            $options['visible'] = FunctionHelper::doCall($visible, $this->config->getDataModel()) ? true : false;
        }

        $this->suppliedOptions = $options;
    }

    /**
     * Adds selects to a query
     *
     * @param array 	$selects
     *
     * @return void
     */
    public function filterQuery(&$selects)
    {
        if ($select = $this->getOption('select')) {
            $selects[] = $this->db->raw($select . ' AS ' . $this->db->getQueryGrammar()->wrap($this->getOption('column_name')));
        }
    }

    /**
     * Gets all user options
     *
     * @return array
     */
    public function getOptions()
    {
        // make sure the supplied options have been merged with the defaults
        if (empty($this->options)) {
            // validate the options and build them
            $this->validateOptions();
            $this->build();
            $this->options = array_merge($this->getDefaults(), $this->suppliedOptions);
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
            throw new \InvalidArgumentException("An invalid option was searched for in the '" . $options['column_name'] . "' column");
        }

        return $options[$key];
    }

    /**
     * Render the Column
     *
     * Takes a column output string and renders the column with it (replacing '(:value)' with
     * the column's field value)
     *
     * If you want your column to show more than just text, you can use the output option.
     * This can either be a string or an anonymous function.
     *
     * If you provide an anonymous function, the arguments available are the relevant column's
     * value from the database, and the current model.
     *
     * @param $value string	$value
     * @param \Illuminate\Database\Eloquent\Model	$item
     *
     * @return string
     * @link https://github.com/ddpro/admin/blob/master/docs/columns.md#custom-outputs
     */
    public function renderOutput($value, $item = null)
    {
        $output = $this->getOption('output');

        if (FunctionHelper::canCall($output)) {
            return FunctionHelper::doCall($output, $value, $item);
        }

        return str_replace('(:value)', $value, $output);
    }

    /**
     * Gets all rules
     *
     * @return array
     */
    public function getRules()
    {
        return array_merge($this->baseRules, $this->rules);
    }

    /**
     * Gets all default values
     *
     * @return array
     */
    public function getDefaults()
    {
        return array_merge($this->baseDefaults, $this->defaults);
    }
}
