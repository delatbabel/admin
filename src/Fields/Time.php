<?php
namespace DDPro\Admin\Fields;

use DateTime as DateTime;
use DDPro\Admin\Config\ConfigInterface;
use DDPro\Admin\Validator;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Time extends Field
{

    /**
     * The specific defaults for subclasses to override
     *
     * The date_format can be left empty, in which case the one in config administrator.format.date_datepicker is
     * used as a fallback.
     *
     * The time_format can be left empty, in which case the one in config administrator.format.time_datepicker is
     * used as a fallback.
     *
     * @var array
     */
    protected $defaults = [
        'min_max'     => true,
        'date_format' => '',
        'time_format' => '',
    ];

    /**
     * The specific rules for subclasses to override
     *
     * @var array
     */
    protected $rules = [
        'date_format' => 'string',
        'time_format' => 'string',
    ];

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
        $model = $this->config->getDataModel();

        // try to read the time for the min and max values, and if they check out, set the where
        if ($minValue = $this->getOption('min_value')) {
            $time = new DateTime($minValue);

            if ($time !== false) {
                $query->where($model->getTable() . '.' . $this->getOption('field_name'), '>=', $this->getDateString($time));
            }
        }

        if ($maxValue = $this->getOption('max_value')) {
            $time = new DateTime($maxValue);

            if ($time !== false) {
                $query->where($model->getTable() . '.' . $this->getOption('field_name'), '<=', $this->getDateString($time));
            }
        }
    }

    /**
     * Fill a model with input data
     *
     * @param \Illuminate\Database\Eloquent\Model	$model
     * @param mixed									$input
     *
     * @return array
     */
    public function fillModel(&$model, $input)
    {
        $time       = false;
        $field_name = $this->getOption('field_name');

        if (empty($input) && $field_name) {
            $model->{$field_name} = null;
            return;
        } elseif (! empty($input) && $input !== '0000-00-00') {
            $PHPFormatOptions = ['Y', 'm', 'd'];
            // There may be a bug with the datepicker we are using - the format 'yyyy' duplicates the year value
            $DatePickerFormatOptions = ['yy', 'mm', 'dd']; // And so on
            if ($this->getOption('date_format')) {
                $format = str_replace($DatePickerFormatOptions, $PHPFormatOptions, $this->getOption('date_format'));
            } else {
                $format = 'Y-m-d';
            }

            $time = DateTime::createFromFormat($format, $input);
        }

        // first we validate that it's a date/time
        if ($time !== false) {
            // fill the model with the correct date/time format
            $model->{$field_name} = $this->getDateString($time);
        }
    }

    /**
     * Get a date format from a time depending on the type of time field this is
     *
     * @param int		$time
     *
     * @return string
     */
    public function getDateString(DateTime $time)
    {
        if ($this->getOption('type') === 'date') {
            return $time->format('Y-m-d');
        } elseif ($this->getOption('type') === 'datetime') {
            return $time->format('Y-m-d H:i:s');
        } else {
            return $time->format('H:i:s');
        }
    }
}
