<?php

namespace DDPro\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use DDPro\Admin\Http\Requests\RoleFormRequest;
use Illuminate\Http\Request;
use Sentinel;

/**
 * Class RoleController
 *
 * This is the Roles controller from Centaur.  It allows CRUD operations on the roles defined
 * by Sentinel.
 *
 * ### Entry Points
 *
 * * index -- display a roles listing
 * * create -- show form to create a role
 * * store -- process POST for creating role
 * * edit -- show an edit form for a role
 * * update -- process POST for editing a role
 * * destroy -- delete a role
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
class RoleController extends Controller
{
    /** @var IlluminateRoleRepository */
    protected $roleRepository;

    public function __construct()
    {
        // Middleware
        $this->middleware('sentinel.auth');
        $this->middleware('sentinel.role:administrator');

        // Fetch the Role Repository from the IoC container
        $this->roleRepository = app()->make('sentinel.roles');
    }

    /**
     * Display a listing of the roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = $this->roleRepository->createModel()->all();

        return view('centaur.roles.index')->with('roles', $roles);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = $this->roleRepository->createModel();

        return view('centaur.roles.form')->with('role', $roles);
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  RoleFormRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleFormRequest $request)
    {
        // Create the Role
        $role = Sentinel::getRoleRepository()->createModel()->create([
            'name' => trim($request->get('name')),
            'slug' => trim($request->get('slug')),
        ]);

        // Cast permissions values to boolean
        $permissions = [];
        foreach ($request->get('permissions', []) as $permission => $value) {
            $permissions[$permission] = (bool)$value;
        }

        // Set the role permissions
        $role->permissions = $permissions;
        $role->save();

        // All done
        session()->flash('success', "Role '{$role->name}' has been created.");

        return redirect()->route('roles.index');
    }

    /**
     * Display the specified role.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // The roles detail page has not been included for the sake of brevity.
        // Change this to point to the appropriate view for your project.
        return redirect()->route('roles.index');
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($role = $this->roleRepository->findById($id)) {
            return view('centaur.roles.form')->with('role', $role);
        }
        session()->flash('error', 'Invalid role.');

        return redirect()->back();
    }

    /**
     * Update the specified role in storage.
     *
     * @param  RoleFormRequest $request
     * @param  string          $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleFormRequest $request, $id)
    {
        // Fetch the role object
        $role = $this->roleRepository->findById($id);
        if (! $role) {
            session()->flash('error', 'Invalid role.');

            return redirect()->back()->withInput();
        }
        // Update the role
        $role->name = $request->get('name');
        $role->slug = $request->get('slug');
        // Cast permissions values to boolean
        $permissions = [];
        foreach ($request->get('permissions', []) as $permission => $value) {
            $permissions[$permission] = (bool)$value;
        }
        // Set the role permissions
        $role->permissions = $permissions;
        $role->save();
        session()->flash('success', "Role '{$role->name}' has been updated.");

        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  Request $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Fetch the role object
        $role = $this->roleRepository->findById($id);
        // Remove the role
        $role->delete();
        // All done
        $message = "Role '{$role->name}' has been removed.";
        session()->flash('success', $message);

        return redirect()->route('roles.index');
    }
}
