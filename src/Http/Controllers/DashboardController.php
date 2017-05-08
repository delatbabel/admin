<?php

namespace DDPro\Admin\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * Handle Dashboard Page for SentinelGuest Middleware
 *
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        // Middleware
        $this->middleware('sentinel.auth');
    }

    /**
     * Check the user type and redirect to their Dashboard
     * route('customer_dashboard') as customer (customer_id != NULL)
     * route('supplier_dashboard') as supplier (supplier_id != NULL)
     * route('admin_dashboard')    as admin    (customer_id and supplier_id both NULL)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function Index()
    {
        // Return the appropriate response
        $expected_dashboard = 'admin_dashboard';
        if (\Sentinel::getUser()) {
            $role_dashboard_mapping = config('administrator.role_dashboard_mapping');
            // Loop through role_dashboard_mapping config, in case a user has many roles, the first appropriate dashboard
            // in the config will be chosen
            foreach ($role_dashboard_mapping as $role => $dashboard) {
                if (\Sentinel::inRole($role)) {
                    $expected_dashboard = $dashboard;
                    break;
                }
            }
        }
        if ($expected_dashboard != '/') {
            $expected_dashboard = route($expected_dashboard);
        }
        return redirect($expected_dashboard);
    }
}
