<?php

namespace DDPro\Admin\Http\Controllers;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Log;

/**
 * Handle Dashboard Page for SentinelGuest Middleware
 *
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
     *
     * This is done by checking the role_dashboard_mapping config.  It should look like this:
     *
     * <code>
     *     'role_dashboard_mapping' => [
     *         'administrator' => [
     *             'route'     => 'admin_dashboard',
     *         ],
     *     'customer'      => [
     *             'url'       => '/',
     *     ],
     * ]; // ... etc
     * </code>
     *
     * Each role can define either a route or a URL to be redirected to.  If both are defined
     * the route takes precedence.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Return the appropriate response
        $expected_dashboard = [
            'route'         => 'admin_dashboard',
        ];
        if (Sentinel::getUser()) {
            $role_dashboard_mapping = config('administrator.role_dashboard_mapping');

            // Loop through role_dashboard_mapping config, in case a user has many roles, the first appropriate dashboard
            // in the config will be chosen
            foreach ($role_dashboard_mapping as $role => $dashboard) {
                if (Sentinel::inRole($role)) {
                    $expected_dashboard = $dashboard;
                    Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                        'dashboard redirect for role ' . $role, $expected_dashboard);
                    break;
                }
            }
        }

        // Redirect to the role's dashboard
        if (! empty($expected_dashboard['route'])) {
            return redirect(route($expected_dashboard['route']));
        }
        return redirect($expected_dashboard['url']);
    }
}
