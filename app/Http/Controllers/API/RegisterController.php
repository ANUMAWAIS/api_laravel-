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
            // Fetch user's role
            // dd($user->getRoleNames());
            $role = $user->getRoleNames()->first();

            // Create an access token
            $accessToken = $user->createToken('MyApp')->accessToken;

            // Prepare the success response with tokens, user information, and role
            $data['token'] = $accessToken;
            $data['user'] = $user;
            $data['role'] = $role;

            return $this->sendResponse($data, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
        }
    }
}