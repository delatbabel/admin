<?php

namespace Delatbabel\Admin\Http\Controllers\Auth;

use Centaur\AuthManager;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Log;

/**
 * Class SessionController
 *
 * This is the Session controller from Centaur.  It handles login and logout.
 *
 * ### Entry Points
 *
 * * getLogin -- display a login form.
 * * postLogin -- handle a login request.
 * * getLogout -- handle a logout request.
 *
 * ### Middleware
 *
 * These middleware names are set up in the CentaurServiceProvider:
 *
 * * $router->middleware('sentinel.guest', \Centaur\Middleware\SentinelGuest::class);
 *   redirects to URL /dashboard if the user is logged in.
 * * $router->middleware('sentinel.auth', \Centaur\Middleware\SentinelAuthenticate::class);
 *   redirects to route auth.login.form if the user is not logged in.
 * * $router->middleware('sentinel.role', \Centaur\Middleware\SentinelUserInRole::class);
 *   redirects back one step if the user is not in a role.
 * * $router->middleware('sentinel.access', \Centaur\Middleware\SentinelUserHasAccess::class);
 *   redirects back one step if the user does not have a specific permission.
 *
 * @see CentaurServiceProvider
 * @link https://github.com/SRLabs/Centaur
 * @link https://github.com/cartalyst/sentinel
 * @link https://cartalyst.com/manual/sentinel/2.0
 */
class SessionController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var AuthManager */
    protected $authManager;

    /**
     * Create a new authentication controller instance.
     */
    public function __construct(AuthManager $authManager)
    {
        $this->middleware('sentinel.guest', ['except' => 'getLogout']);
        $this->authManager = $authManager;
    }

    /**
     * Show the Login Form
     * @return View
     */
    public function getLogin()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'get login page centaur.auth.login');
        return view('centaur.auth.login');
    }

    /**
     * Handle a Login Request
     * @return Response
     */
    public function postLogin(Request $request)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'POST Login for email ' . trim($request->get('email')));

        // Validate the Form Data
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required'
        ]);

        // Assemble Login Credentials
        $credentials = [
            'email'    => trim($request->get('email')),
            'password' => $request->get('password'),
        ];
        $remember = (bool)$request->get('remember', false);

        // Attempt the Login
        $result = $this->authManager->authenticate($credentials, $remember);
        if ($result->isFailure()) {
            // Redirect to login form if authentication failure
            $path = config('app.login_form_url') ? config('app.login_form_url') : route('auth.login.form');
        } else {
            $path = session()->pull('url.intended', route('dashboard'));
        }
        return $result->dispatch($path);
    }

    /**
     * Handle a Logout Request
     * @return Response
     */
    public function getLogout(Request $request)
    {
        // Terminate the user's current session.  Passing true as the
        // second parameter kills all of the user's active sessions.
        $result = $this->authManager->logout(null, null);

        // Return the appropriate response
        return $result->dispatch(route('dashboard'));
    }
}
