<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\UserRoleController;
use App\Models\User;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
     
// Route::middleware('auth:api')->group( function () {
//     Route::resource('products', ProductController::class);
// });

Route::middleware(['auth:api'])->group(function () {
    Route::get('/user/{id}/permissions', function ($id) {
        $user = User::find($id);
        return response()->json($user->getAllPermissions());
    });
});


//Define a routes accroding to user access.
//Admin routes
Route::group(['middleware' => ['auth:api', 'role:admin']], function () {

    Route::post('assign-role', [UserRoleController::class, 'assignRole']);
    Route::get('view-role', [UserRoleController::class, 'viewRole']);
    //User Routes.
    Route::post('role', [UserRoleController::class, 'createRole']);
    Route::post('permissions', [UserRoleController::class, 'assignPermissionsWithName']);
    Route::post('roleHavePermissions', [UserRoleController::class, 'roleHavePermissions']);
    Route::post('admin-register', [UserRoleController::class, 'register']);

//   Route::post('user-have-permition', [UserRoleController::class, 'userHavePermition']);
Route::middleware('auth:api')->get('/user/roles-permissions', [UserRoleController::class, 'getUserRolesAndPermissions']);
});


  