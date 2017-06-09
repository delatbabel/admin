<?php

namespace DDPro\Admin\Http\Controllers\Auth;

use Activation;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Centaur\AuthManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mail;
use Sentinel;
use Session;

/**
 * Class RegistrationController
 *
 * This is the Registration controller from Centaur.  It allows user self-registration.
 *
 * ### Entry Points
 *
 * * getRegister -- display the registration form.
 * * postRegister -- handle a registration request.
 * * getActivate -- handle an activation request.
 * * getResend -- display the form to resend the activation email
 * * postResend -- resend the activation email
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
class RegistrationController extends Controller
{
    /** @var AuthManager */
    protected $authManager;

    /**
     * Create a new authentication controller instance.
     */
    public function __construct(AuthManager $authManager)
    {
        $this->middleware('sentinel.guest');
        $this->authManager = $authManager;
    }

    /**
     * Show the registration form
     * @return View
     */
    public function getRegister()
    {
        return view('centaur.auth.register');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return Response
     */
    protected function postRegister(Request $request)
    {
        // Validate the form data
        $this->validate($request, [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        // Assemble registration credentials
        $credentials = [
            'email'    => trim($request->get('email')),
            'password' => $request->get('password'),
        ];

        // Attempt the registration
        $result = $this->authManager->register($credentials);

        if ($result->isFailure()) {
            return $result->dispatch();
        }

        // Send the activation email
        $code  = $result->activation->getCode();
        $email = $result->user->email;

        // Sender address comes from mail configuration file and .env
        Mail::queue(
            'centaur.email.welcome',
            ['code' => $code, 'email' => $email],
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your account has been created');
            }
        );

        // Ask the user to check their email for the activation link
        $result->setMessage('Registration complete.  Please check your email for activation instructions.');

        // There is no need to send the payload data to the end user
        $result->clearPayload();

        // Return the appropriate response
        return $result->dispatch(route('dashboard'));
    }

    /**
     * Activate a user if they have provided the correct code
     * @param  string $code
     * @return Response
     */
    public function getActivate(Request $request, $code)
    {
        // Attempt the registration
        $result = $this->authManager->activate($code);

        if ($result->isFailure()) {
            // Normally an exception would trigger a redirect()->back() However,
            // because they get here via direct link, back() will take them
            // to "/";  I would prefer they be sent to the login page.
            $result->setRedirectUrl(route('auth.login.form'));
            return $result->dispatch();
        }

        // Ask the user to check their email for the activation link
        $result->setMessage('Registration complete.  You may now log in.');

        // There is no need to send the payload data to the end user
        $result->clearPayload();

        // Return the appropriate response
        return $result->dispatch(route('dashboard'));
    }

    /**
     * Show the Resend Activation form
     * @return View
     */
    public function getResend()
    {
        return view('centaur.auth.resend');
    }

    /**
     * Handle a resend activation request
     * @return Response
     */
    public function postResend(Request $request)
    {
        // Validate the form data
        $this->validate($request, [
            'email' => 'required|email|max:255|exists:users'
        ]);

        // Fetch the user in question
        $user = Sentinel::findUserByCredentials(['email' => $request->get('email')]);

        // Only send them an email if they have a valid, inactive account
        if (! Activation::completed($user)) {
            // Generate a new code
            $activation = Activation::create($user);

            // Send the email
            $code  = $activation->getCode();
            $email = $user->email;
            Mail::queue(
                'auth.email.welcome',
                ['code' => $code, 'email' => $email],
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Account Activation Instructions');
                }
            );
        }

        $message = 'New instructions will be sent to that email address if it is associated with a inactive account.';

        if ($request->ajax()) {
            return response()->json(['message' => $message], 200);
        }

        Session::flash('success', $message);
        return redirect(route('dashboard'));
    }
}
