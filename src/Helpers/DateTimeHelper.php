<?php

namespace DDPro\Admin\Helpers;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

/**
 * Class DateTimeHelper
 *
 * Helper functions for dealing with datetimes, timezones and formatting.
 *
 * ### Example
 *
 * <code>
 * // Example code goes here
 * </code>
 *
 * @see  ...
 * @link ...
 */
class DateTimeHelper
{
    /**
     * Get the admin time zone or the system time zone if the admin is not logged in.
     *
     * @return \DateTimeZone
     */
    public static function adminTimeZone()
    {
        $tz = new \DateTimeZone(config('app.timezone'));
        $user = Sentinel::check();
        if (! empty($user) && ! empty($user->timezone)) {
            $tz = new \DateTimeZone($user->timezone);
        }

        return $tz;
    }

    /**
     * Format a datetime into a value suitable for editing by the current admin.
     *
     * Takes either a DateTime or a Carbon or a string in the system time zone,
     * e.g. a value retrieved from a database.
     *
     * Returns a string in the admin's time zone which can be used as an edit field
     * seeder.
     *
     * @param $value
     * @return null|string
     */
    public static function formatDateTimeForEdit($value)
    {
        $tz = static::adminTimeZone();

        if (empty($value)) {
            $tmpValue = null;
        } elseif ($value instanceof \DateTime) {
            $tmpValue = $value->setTimezone($tz)->format(config('administrator.format.datetime_carbon'));
        } else {
            $dt       = new \DateTime($value);
            $tmpValue = $dt->setTimezone($tz)->format(config('administrator.format.datetime_carbon'));
        }

        return $tmpValue;
    }

    /**
     * Format a datetime into a value suitable for display to the current admin.
     *
     * Takes either a DateTime or a Carbon or a string in the system time zone,
     * e.g. a value retrieved from a database.
     *
     * Returns a string in the admin's time zone which can be used to display the
     * date and time, e.g. in a column listing or an export.
     *
     * @param $value
     * @return string
     */
    public static function formatDateTimeForDisplay($value)
    {
        $tz = static::adminTimeZone();

        // $dt object will be in server time zone
        if ($value instanceof \DateTime) {
            $dt = $value;
        } else {
            $dt = new \DateTime($value);
        }

        // Convert to user timezone to display
        if (! empty($tz)) {
            $dt->setTimezone($tz);
        }
        return $dt->format(config('administrator.format.datetime_carbon'));
    }
}
