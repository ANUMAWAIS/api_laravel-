<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;


class UserRoleController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255', 
            'email' => 'required|email|unique:users', 
            'password' => 'required|min:8', 
            'role_id' => 'required|exists:roles,id',
        ]);
    
        // Check if the validation fails
        if ($validator->fails()) {
            // Return a JSON response with the validation errors and a 422 status code
            return response()->json(['error' => 'Validation Error.', 'message' => $validator->errors()], 422);
        }
    
        // Create the new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, 
            'password' => bcrypt($request->password), 
        ]);
    
        // Assign the specified role to the user using role_id
        $role = Role::findById($request->role_id); 
        $user->assignRole($role); // uses Spatie's built-in method to assign a role to a user.
    
        // Create a token for the user (assuming Passport or Sanctum is used for API authentication)
        $success['token'] =  $user->createToken('MyApp')->accessToken; // Generate an API token for the user
        $success['name'] =  $user->name; 
    
     
        return response()->json(['message' => 'User registered successfully', 'user' => $user, 'token' => $success['token']], 201);
    }

    //Assign role to user
    public function assignRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json('Validation Error.', $validator->errors());
        }
        $user = User::find($request->user_id);

        //Check user is exist or not.
        if ($user !== null) {
            //Check Persmission is exist or not.
            $isRoleExist = Role::findByName($request->role_name);
            if ($isRoleExist) {
                $isRoleExist->users()->attach($user);
                return response()->json("Role assigned successfully!");
            } else {
                return response()->json("Role Not Found");
            }
        } else {
            return response()->json("User Not Found");
        }
        //$user->toArray
    }


    //View roles
    public function viewRole()
    {
        // // Retrieve all roles from the database
        // $roles = Role::all();

        // // Return roles as a JSON response
        // return response()->json(['roles' => $roles]);
        $roleNames = Role::pluck('name');

        // Return role names as a JSON response
        return response()->json(['roles' => $roleNames]);
    }

    //create a new role
    public function createRole(Request $request)
    { {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'role_name' => ['required', 'string', 'max:255', 'unique:roles,name', 'regex:/^[a-zA-Z\s]+$/'],
            ], [
                'role_name.required' => 'Role name is required.',
                'role_name.string' => 'Role name must be a string.',
                'role_name.max' => 'Role name must not exceed 255 characters.',
                'role_name.unique' => 'Role name must be unique.',
                'role_name.regex' => 'Role name must contain only alphabetic characters and spaces.',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                // Return validation error response with custom message
                return response()->json(['error' => $validator->errors()], 422);
            }

            try {
                // Create the new role
                $role = Role::create(['name' => $request->input('role_name')]);

                // Role created successfully, return success response
                return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
            } catch (\Exception $e) {
                // An error occurred while creating the role, return error response
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }
    //assign permitions
    public function assignPermissionsWithName(Request $request)
    {
        // dd($request->all());
        // Validate the request
        if ($request->has('permissions') && is_string($request->input('permissions'))) {
            $permissions = explode(',', $request->input('permissions'));
            $request->merge(['permissions' => $permissions]);
        }
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error.', 'message' => $validator->errors()], 422);
        }

        try {

            $role = Role::where('id', $request->role_id)->where('guard_name', 'api')->firstOrFail();
            // dd($role);
            // Attach the permissions to the role with the specified guard
            // dd($request->permissions);
            $permissions = Permission::whereIn('name', $request->permissions)
                ->where('guard_name', 'api')
                ->get();

            // Give permissions to the role\

            $role->syncPermissions($permissions);
            // dd($role);
            return response()->json(['message' => 'Permissions assigned successfully.', 'role' => $role,  'permissions' => $permissions]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // userHavePermition 
    public function roleHavePermissions(Request $request)
    {
        // dd($request->toArray());
        // Validate the request
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'permission_id' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error.', 'message' => $validator->errors()], 422);
        } else {
            // Validation passed, print success message and values
            $roleId = $request->role_id;
            $permissionIds = $request->permission_id;

            echo "Validation successful! Role ID: $roleId, Permission IDs: " . implode(', ', $permissionIds);
        }
        try {
            // Find the role
            $role = Role::findOrFail($request->role_id);
            //  dd($role->toArray());
            // dd($role);   
            // If permission_id is not an array, convert it to an array
            // $permissionIds = is_array($request->permission_ids) ? $request->permission_ids : [$request->permission_ids];

            //  dd($permissionIds);

            // Attach the permissions to the role
            $permissionIds = Permission::whereIn('id', $permissionIds)->get();
            $role->syncPermissions($permissionIds);

            return response()->json(['message' => 'Permissions assigned successfully.', 'role' => $role, 'permissions' => $permissionIds]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function getUserRolesAndPermissions()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Get the roles assigned to the user
        $roles = $user->getRoleNames();
        
        // Get the permissions directly assigned to the user
        // $permissions = $user->getDirectPermissions()->pluck('name');

        // Get the permissions inherited from roles
         $rolePermissions = $user->getAllPermissions()->pluck('name');

        // Return the roles and permissions as JSON response
        return response()->json([
            'roles' => $roles,
            // 'permissions' => $permissions,
            'role_permissions' => $rolePermissions,
        ]);
        return response()->json([
            'permissions' => $permissions,
        ]);
    }
}
