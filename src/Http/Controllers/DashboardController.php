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
        // FIXME: Update the router for each user type
        // $user = \Sentinel::getUser();
        // if ($user->contact_id) {
        //     return redirect()->route('customer_dashboard');
        // }
        // if ($user->supplier_id) {
        //     return redirect()->route('supplier_dashboard');
        // }

        return redirect()->route('admin_dashboard');
    }
}
