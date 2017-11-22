<?php
namespace Delatbabel\Admin\Http\Middleware;

use Closure;
use Delatbabel\Admin\Helpers\FunctionHelper;
use Exception;
use Log;

/**
 * Class ValidateAdmin
 *
 * This middleware performs permission checking by testing the administrator.permission closure
 * for a response as to whether the currently logged in user is entitled to access the current
 * request.
 */
class ValidateAdmin
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // get the admin check function that should be supplied in the config
        /** @var string $permission */
        $permission = config('administrator.permission');

        try {
            // Check the response from the permission function
            $response = FunctionHelper::doCall($permission);
        } catch (Exception $e) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'An exception occurred while checking permission: ' . $e);

            return $next($request);
        }

        // if this is a simple false value, send the user to the login redirect
        if (! $response) {
            $loginUrl    = url(config('administrator.login_path', 'user/login'));
            $redirectKey = config('administrator.login_redirect_key', 'redirect');
            $redirectUri = $request->url();

            return redirect()->guest($loginUrl)->with($redirectKey, $redirectUri);
        } elseif (is_a($response, 'Illuminate\Http\JsonResponse') || is_a($response, 'Illuminate\Http\Response')) {
            // otherwise if this is a response, return that
            return $response;
        } elseif (is_a($response, 'Illuminate\\Http\\RedirectResponse')) {
            // if it's a redirect, send it back with the redirect uri
            $redirectKey = config('administrator.login_redirect_key', 'redirect');
            $redirectUri = $request->url();

            return $response->with($redirectKey, $redirectUri);
        }

        // If none of the above then ignore the response and continue
        return $next($request);
    }
}
