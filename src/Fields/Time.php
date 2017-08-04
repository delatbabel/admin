<?php
namespace DDPro\Admin\Fields;

use DateTime as DateTime;
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
        }

        $field_type = $this->getOption('type');

        if ($field_type == 'time') {
            $time_format = !empty($this->getOption('time_format')) ? $this->getOption('time_format') : config('administrator.format.time_carbon');
            // The $time_format in the option will be the datepicker format, we have to convert that to a carbon format.
            $PHPFormatOptions           = ['H', 'i', 's'];
            $DatePickerFormatOptions    = ['HH', 'mm', 'ss'];
            $time_format                = str_replace($DatePickerFormatOptions, $PHPFormatOptions, $time_format);
            $time = DateTime::createFromFormat($time_format, $input);
        } elseif (! empty($input) && $input !== '0000-00-00') {
            $date_format = $this->getOption('date_format');

            // date_format falls back to the config variable if not set -- the config already has the carbon formatted
            // date in it.
            if (empty($date_format)) {
                $date_format = config('administrator.format.date_carbon');
            } else {
                // The date_format in the option will be the datepicker format, we have to convert that to a carbon
                // format.
                $PHPFormatOptions        = ['Y', 'm', 'd'];
                $DatePickerFormatOptions = ['yy', 'mm', 'dd'];
                $date_format             = str_replace($DatePickerFormatOptions, $PHPFormatOptions, $date_format);
            }

            // Final fallback
            if (empty($date_format)) {
                $date_format = 'Y-m-d';
            }

            $time = DateTime::createFromFormat($date_format, $input);
        }

        // first we validate that it's a date/time
        if (! empty($time) && ($time instanceof DateTime)) {
            // fill the model with the correct date/time format
            $model->{$field_name} = $this->getDateString($time);
        }
    }

    /**
     * Get the correct date string.
     *
     * Get a date format in the correct format to be saved to the database from a DateTime
     * depending on the type of time field this is.
     *
     * Strictly speaking this should not be necessary because Laravel is capable of handling
     * date fields being assigned DateTime objects directly.
     *
     * @param DateTime $time
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
