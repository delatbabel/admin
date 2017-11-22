<?php

namespace Delatbabel\Admin\Services;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Sentinel\Users\UserInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

/**
 * Class User
 *
 * This is the User service that handles all of the User functionality
 * for display.
 *
 * ### Functionality
 *
 * ### Example
 *
 * <code>
 * @inject('user', 'App\Services\User')
 * {{ $user->fullName() }}
 * </code>
 *
 * @link https://laravel.com/docs/5.1/blade#service-injection
 * @link https://github.com/cartalyst/sentinel
 * @link https://cartalyst.com/manual/sentinel/2.0
 */
class User extends Fluent
{

    /** @var Request  */
    protected $request;

    /** @var UserInterface */
    protected $user;

    /**
     * User Service constructor.
     *
     * @param array   $attributes
     * @param Request $request
     */
    public function __construct($attributes = [], Request $request)
    {
        parent::__construct($attributes);

        $this->request          = $request;
        $this->user             = Sentinel::check();

        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Request session data', $request->session()->all());
    }

    /**
     * Get the currently logged in user, or false if there is no logged in user.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the currently logged in user's full name.
     *
     * @return string
     */
    public function fullName()
    {
        if (empty($this->user)) {
            return "Nobody";
        }

        return $this->user->first_name . ' ' . $this->user->last_name;
    }

    /**
     * Get the currently logged in user's time zone.
     *
     * @return string
     */
    public function timeZone()
    {
        if (empty($this->user)) {
            return "UTC";
        }

        return $this->user->timezone;
    }
}
