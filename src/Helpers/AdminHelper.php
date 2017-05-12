<?php

namespace DDPro\Admin\Helpers;

class AdminHelper {
    public static function detectAdminAccess() {
        if ($user = \Sentinel::check()) {
            return (isset($user->contact_id) || isset($user->supplier_id)) ? false : true;
        }
        return false;
    }
}