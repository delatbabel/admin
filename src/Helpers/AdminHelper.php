<?php

namespace DDPro\Admin\Helpers;

class AdminHelper
{
    /**
     * Detect if a logged in user can access Admin side or not
     *
     */
    public static function detectAdminAccess()
    {
        if ($user = \Sentinel::check()) {
            // Only user without contact_id and supplier_id can access Admin side
            return (isset($user->contact_id) || isset($user->supplier_id)) ? false : true;
        }
        // Default to false
        return false;
    }

    /**
     * Generate checkbox html to display in datatable
     *
     * @param $value
     * @param $model
     * @return string
     */
    public static function getOutputForCheckbox($value, $model)
    {
        return '<input type="checkbox" class="deleteRow" value="' . $model->getAttribute($model->getKeyName()) . '">';
    }

    /**
     * Validate JSON String
     *
     * @param $string
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * jTraceEx() - provide a Java style exception trace
     *
     * @param \Exception $e
     * @param array $seen  leave as NULL when calling this function
     *
     * @return string
     * @link http://php.net/manual/en/exception.gettraceasstring.php#114980
     */
    public static function jTraceEx(\Exception $e, $seen = null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result  = [];
        if (!$seen) {
            $seen = [];
        }
        $trace    = $e->getTrace();
        $prev     = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file     = $e->getFile();
        $line     = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line);
            if (is_array($seen)) {
                $seen[] = "$file:$line";
            }
            if (!count($trace)) {
                break;
            }
            $file = array_key_exists('file', $trace[0]) ?
                $trace[0]['file'] :
                'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ?
                $trace[0]['line'] :
                null;
            array_shift($trace);
        }
        $result = join("\n", $result);
        if ($prev) {
            $result  .= "\n" . static::jTraceEx($prev, $seen);
        }

        return $result;
    }
}
