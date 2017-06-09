<?php

namespace DDPro\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Centaur\AuthManager;
use DDPro\Admin\Http\Requests\UserFormRequest;
use Delatbabel\Keylists\Models\Keytype;
use Delatbabel\Keylists\Models\Keyvalue;
use Mail;
use Sentinel;

/**
 * Class UserController
 *
 * This is the Users controller from Centaur.  It allows CRUD operations on the users defined
 * by Sentinel.
 *
 * ### Entry Points
 *
 * * index -- display a users listing
 * * create -- show form to create a user
 * * store -- process POST for creating user
 * * edit -- show an edit form for a user
 * * update -- process POST for editing a user
 * * destroy -- delete a user
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
class UserController extends Controller
{
    /** @var IlluminateUserRepository */
    protected $userRepository;

    /** @var AuthManager */
    protected $authManager;

    public function __construct(AuthManager $authManager)
    {
        // Middleware
        $this->middleware('sentinel.auth');
        $this->middleware('sentinel.access:users.create', ['only' => ['create', 'store']]);
        $this->middleware('sentinel.access:users.view', ['only' => ['index', 'show']]);
        $this->middleware('sentinel.access:users.update', ['only' => ['edit', 'update']]);
        $this->middleware('sentinel.access:users.delete', ['only' => ['destroy']]);

        // Dependency Injection
        $this->userRepository = app()->make('sentinel.users');
        $this->authManager    = $authManager;
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Admin users are those which are not customers (with a contact_id) and are
        // not a supplier (with a supplier_id).
        $users = User::whereNull('contact_id')
            ->whereNull('supplier_id')
            ->get();

        // Fetch the country list.
        $countryList = Keytype::where('name', 'countries')
            ->firstOrFail()
            ->keyvalues()->orderBy('keyvalue')->get()
            ->lists('keyname', 'keyvalue')
            ->toArray();

        return view('centaur.users.index', [
            'users'       => $users,
            'countryList' => $countryList,
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Fetch the available roles
        $roles = app()->make('sentinel.roles')
            ->createModel()
            ->all();

        // Get country List
        $countryList  = KeyValue::getKeyValuesByType('countries');
        $timezoneList = KeyValue::getKeyValuesByType('timezones');

        return view('centaur.users.create', [
            'roles'        => $roles,
            'countryList'  => $countryList,
            'timezoneList' => $timezoneList,
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  UserFormRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserFormRequest $request)
    {
        $activate = (bool)$request->get('active', false);
        // Attempt the registration
        $result = $this->authManager->register($request->all(), $activate);

        if ($result->isFailure()) {
            return $result->dispatch();
        }
        // Do we need to send an activation email?
        if (! $activate) {
            $code  = $result->activation->getCode();
            $email = $result->user->email;
            Mail::queue('centaur.email.welcome', ['code' => $code, 'email' => $email],
                function ($message) use ($email) {
                    $message->to($email)->subject('Your account has been created');
                }
            );
        }
        // Assign User Roles
        foreach ($request->get('roles', []) as $slug => $id) {
            $role = Sentinel::findRoleBySlug($slug);
            if ($role) {
                $role->users()->attach($result->user);
            }
        }
        $result->setMessage("User {$request->get('email')} has been created.");

        return $result->dispatch(route('users.index'));
    }

    /**
     * Display the specified user.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect()->route('users.index', [$id]);
    }

    /**
     * Display the specified user.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the user object
        $user = User::findOrFail($id);
        if ($user) {
            // Fetch the available roles
            $roles = app()->make('sentinel.roles')
                ->createModel()
                ->all();

            // Get country List
            $countryList  = KeyValue::getKeyValuesByType('countries');
            $timezoneList = KeyValue::getKeyValuesByType('timezones');

            return view('centaur.users.edit', [
                'user'         => $user,
                'roles'        => $roles,
                'countryList'  => $countryList,
                'timezoneList' => $timezoneList,
            ]);
        }
        session()->flash('error', 'Invalid user.');

        return redirect()->back();
    }

    /**
     * Update the specified user in storage.
     *
     * @param  UserFormRequest $request
     * @param  string          $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserFormRequest $request, $id)
    {
        $user = User::findOrFail($id);
        // Update the user
        $user = $this->userRepository->update($user, $request->all());
        // Update role assignments
        if (is_array($request->roles)) {
            $user->roles()->sync($request->roles);
        } else {
            $user->roles()->detach();
        }
        // All done
        session()->flash('success', "{$user->email} has been updated.");

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->userRepository->findById($id);
        // Remove the user
        $user->delete();
        // All done
        session()->flash('success', "{$user->email} has been removed.");

        return redirect()->route('users.index');
    }
}
