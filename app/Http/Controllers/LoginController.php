<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Database\QueryException;
use App\Models\User;
use App\Models\Role;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        try{
            $user = User::login($request->email, $request->password);

            if(!$user) {
                return response()->json(["User not found"], 404);
            }

            $role_name = Role::getRole($user->role_id)->role_name;
            $token = $user->createToken('my-app-token', ["$role_name"])->plainTextToken;           
            
            return response()->json(
                ["user" => $user,
                "token" => $token]
            );
        } 
        catch(QueryException $e) {
            return response($e->getMessage(),400);
        }
    }
}
