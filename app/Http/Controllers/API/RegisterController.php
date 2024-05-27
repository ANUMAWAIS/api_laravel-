<?php



namespace App\Http\Controllers\API;



use Illuminate\Http\Request;

use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;


class RegisterController extends BaseController

{

    /**

     * Register api

     *

     * @return \Illuminate\Http\Response

     */

    public function register(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'name' => 'required',

            'email' => 'required|email',

            'password' => 'required',

            'c_password' => 'required|same:password',


        ]);



        if ($validator->fails()) {

            return $this->sendError('Validation Error.', $validator->errors());
        }



        $input = $request->all();

        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        $success['token'] =  $user->createToken('MyApp')->accessToken;

        $success['name'] =  $user->name;



        return $this->sendResponse($success, 'User register successfully.');
    }



    /**

     * Login api

     *

     * @return \Illuminate\Http\Response

     */

    // public function login(Request $request)

    // {

    //     if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 

    //         $user = Auth::user(); 

    //         $success['token'] =  $user->createToken('MyApp')-> accessToken; 
    //         $success['name'] =  $user->name;



    //         return $this->sendResponse($success, 'User login successfully.');

    //     } 

    //     else{ 

    //         return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);

    //     } 

    // }


    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            // Get the roles assigned to the user
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
            $roles = $user->getRoleNames();
            $rolePermissions = $user->getAllPermissions();

            // Create an access token
            $accessToken = $user->createToken('MyApp')->accessToken;

            // Prepare the success response with tokens, user information, roles, and permissions
            $data['token'] = $accessToken;
            $data['user'] = $userData;
            $data['roles'] = $roles;
            $data['rolePermissions'] = $rolePermissions;


            return $this->sendResponse('User login successfully.', $data);
        } else {
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
        }
    }
}
