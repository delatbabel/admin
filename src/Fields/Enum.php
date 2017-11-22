<?php
namespace Delatbabel\Admin\Fields;

use Illuminate\Database\Query\Builder as QueryBuilder;

class Enum extends Field
{

    /**
     * The options used for the enum field
     *
     * @var array
     */
    protected $rules = [
        // Fixme: this validation seems not working
        'callback' => 'required_without:options|array|not_empty',
        'options'  => 'required_without:callback|array|not_empty',
    ];

    /**
     * Builds a few basic options
     */
    public function build()
    {
        parent::build();

        $options = $this->suppliedOptions;

        if (isset($options['callback'])) {
            $dataOptions = call_user_func_array([$options['callback']['class'], $options['callback']['method']], isset($options['callback']['params']) ? $options['callback']['params'] : []);
        } else {
            $dataOptions = $options['options'];
        }
        $options['options'] = [];

        // iterate over the options to create the options assoc array
        foreach ($dataOptions as $val => $text) {
            $options['options'][] = [
                'id'   => $val,
                'text' => $text,
            ];
        }

        $this->suppliedOptions = $options;
    }

    /**
     * Fill a model with input data
     *
     * @param \Illuminate\Database\Eloquent\model	$model
     * @param mixed									$input
     */
    public function fillModel(&$model, $input)
    {
        $model->{$this->getOption('field_name')} = $input;
    }

    /**
     * Sets the filter options for this item
     *
     * @param array		$filter
     *
     * @return void
     */
    public function setFilter($filter)
    {
        parent::setFilter($filter);

        $this->userOptions['value'] = $this->getOption('value') === '' ? null : $this->getOption('value');
    }

    /**
     * Filters a query object
     *
     * @param \Illuminate\Database\Query\Builder	$query
     * @param array									$selects
     *
     * @return void
     */
    public function filterQuery(QueryBuilder &$query, &$selects = null)
    {
        // run the parent method
        parent::filterQuery($query, $selects);

        // if there is no value, return
        if ($this->getFilterValue($this->getOption('value')) === false) {
            return;
        }

        $query->where($this->config->getDataModel()->getTable() . '.' . $this->getOption('field_name'), '=', $this->getOption('value'));
    }
}
