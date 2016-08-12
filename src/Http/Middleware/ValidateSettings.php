<?php
namespace DDPro\Admin\Http\Middleware;

use Closure;

/**
 * Class ValidateSettings
 *
 * The primary task of this middleware is to set the itemconfig middlewhere for cases
 * where the system settings are under administration.
 */
class ValidateSettings
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
        $settingsName = $request->route()->parameter('settings');

        app()->singleton('itemconfig', function ($app) use ($settingsName) {
            $configFactory = app('admin_config_factory');
            return $configFactory->make($configFactory->getSettingsPrefix() . $settingsName, true);
        });

        return $next($request);
    }
}
