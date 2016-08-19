<?php
namespace DDPro\Admin\Http\Middleware;

use Closure;

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
        $configFactory = app('admin_config_factory');

        // get the admin check closure that should be supplied in the config
        /** @var Closure $permission */
        $permission = config('administrator.permission');

        // if this is a simple false value, send the user to the login redirect
        if (!$response = $permission()) {
            $loginUrl    = url(config('administrator.login_path', 'user/login'));
            $redirectKey = config('administrator.login_redirect_key', 'redirect');
            $redirectUri = $request->url();

            return redirect()->guest($loginUrl)->with($redirectKey, $redirectUri);
        }

        // otherwise if this is a response, return that
        elseif (is_a($response, 'Illuminate\Http\JsonResponse') || is_a($response, 'Illuminate\Http\Response')) {
            return $response;
        }

        // if it's a redirect, send it back with the redirect uri
        elseif (is_a($response, 'Illuminate\\Http\\RedirectResponse')) {
            $redirectKey = config('administrator.login_redirect_key', 'redirect');
            $redirectUri = $request->url();

            return $response->with($redirectKey, $redirectUri);
        }

        return $next($request);
    }
}
