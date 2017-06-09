<?php

namespace DDPro\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Centaur\AuthManager;
use DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Mail;
use Reminder;
use Sentinel;
use Session;

/**
 * Class PasswordController
 *
 * This is the Password controller from Centaur.  It allows forgotten password handling.
 *
 * ### Entry Points
 *
 * * getRequest -- display the forgot password form.
 * * postRequest -- handle a POST to the forgot password form.
 * * getReset -- show the password reset form.
 * * postReset -- handle a POST to the password reset form.
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
 * See the __construct() function for usage examples of these middleware.
 *
 * @link https://github.com/SRLabs/Centaur
 * @link https://github.com/cartalyst/sentinel
 * @link https://cartalyst.com/manual/sentinel/2.0
 */
class PasswordController extends Controller
{
    /** @var AuthManager */
    protected $authManager;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(AuthManager $authManager)
    {
        $this->middleware('sentinel.guest');
        $this->authManager = $authManager;
    }

    /**
     * Show the password reset request form
     * @return View
     */
    public function getRequest()
    {
        return view('centaur.auth.reset');
    }

    /**
     * Send a password reset link
     * @return Response
     */
    public function postRequest(Request $request)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'POST password reset request', $request->all());
        // Validate the form data
        $this->validate($request, [
            'email' => 'required|email|max:255'
        ]);

        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'validation OK');

        // Fetch the user in question
        $user = Sentinel::findUserByCredentials(['email' => $request->get('email')]);

        // Only send them an email if they have a valid, inactive account
        if ($user) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'user found, User ID = ' . $user->id);
            // Generate a new code
            $reminder = Reminder::create($user);

            // Send the email
            $code  = $reminder->code;
            $email = $user->email;

            // Sender address comes from mail configuration file and .env
            Mail::queue(
                'centaur.email.reset',
                ['code' => $code],
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Password Reset Link');
                }
            );
        }

        $message = 'Instructions for changing your password will be sent to your email address if it is associated with a valid account.';

        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'message OK');

        if ($request->ajax()) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'password reset response AJAX');
            return response()->json(['message' => $message, 'code' => $code], 200);
        }

        Session::flash('success', $message);
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'password reset return to dashboard');
        return redirect(route('auth.password.request.form'));
    }

    /**
     * Show the password reset form if the reset code is valid
     * @param  Request $request
     * @param  string  $code
     * @return View
     */
    public function getReset(Request $request, $code)
    {
        // Is this a valid code?
        if (! $this->validatePasswordResetCode($code)) {
            // This route will not be accessed via ajax;
            // no need for a json response
            Session::flash('error', 'Invalid or expired password reset code; please request a new link.');
            return redirect()->route('auth.password.request.form');
        }

        return view('centaur.auth.password')
            ->with('code', $code);
    }

    /**
     * Process a password reset form submission
     * @param  Request $request
     * @param  string  $code
     * @return Response
     */
    public function postReset(Request $request, $code)
    {
        // Validate the form data
        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ]);

        // Attempt the password reset
        $result = $this->authManager->resetPassword($code, $request->get('password'));

        // Return the appropriate response
        return $result->dispatch(route('auth.login.form'));
    }

    /**
     * @param  string $code
     * @return boolean
     */
    protected function validatePasswordResetCode($code)
    {
        return DB::table('reminders')
                ->where('code', $code)
                ->where('completed', false)->count() > 0;
    }
}
