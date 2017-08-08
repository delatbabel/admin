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
}
