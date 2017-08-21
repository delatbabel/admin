<?php
namespace DDPro\Admin\Fields;

use Illuminate\Database\Query\Builder as QueryBuilder;

class ArrayText extends Field
{

    /**
     * The specific defaults for subclasses to override
     *
     * @var array
     */
    protected $defaults = [
        'limit'  => null,
        'height' => 20,
    ];

    /**
     * The specific rules for subclasses to override
     *
     * @var array
     */
    protected $rules = [
        'limit'  => 'integer|min:0',
        'height' => 'integer|min:0',
    ];

    /**
     * Filters a query object given
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

        $query->where($this->config->getDataModel()->getTable() . '.' . $this->getOption('field_name'), 'LIKE', '%' . $this->getOption('value') . '%');
    }

    /**
     * Fill a model with input data
     *
     * @param \Illuminate\Database\Eloquent\model	$model
     * @param mixed									$input
     * @return void
     */
    public function fillModel(&$model, $input)
    {
        $model->{$this->getOption('field_name')} = $input;
    }
}
