<?php
namespace DDPro\Admin\Http\Middleware;

use Closure;

/**
 * Class ValidateModel
 *
 * The primary task of this middleware is to set the itemconfig middlewhere for cases
 * where a model is under administration.
 */
class ValidateModel
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
        $modelName = $request->route()->parameter('model');

        app()->singleton('itemconfig', function ($app) use ($modelName) {
            $configFactory = app('admin_config_factory');

            return $configFactory->make($modelName, true);
        });

        return $next($request);
    }
}
