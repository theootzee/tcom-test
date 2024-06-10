<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::getAll();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, User $user)
    {
        if($request->user()->tokenCan('admin')) {
            
            $user->email = $request->email;
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->password = sha1("Test12345");
            $user->role_id = $request->role_id;
            $request->role_id != 1 ? $user->team_id = $request->team_id : $user->team_id = null;
            $teams = $request->teams;

            try{
                $user->save();
                if($user->role_id == 2) {
                    foreach ($teams as $team) {
                        $user->teams()->attach($team);
                    }
                }
                $token = $user->createToken('my-app-token')->plainTextToken;
                User::saveToken($token, $user->id);
                return response()->json([
                "message" => 'User created!', 
                "user" => $user, 
                "token" => $token
                ], 201 );
            }
            catch(UniqueConstraintViolationException $e) {
                Log::critical($e->getMessage());
                return response("Email already in use", 422);
            }
            catch(QueryException $e) {
                Log::critical($e->getMessage());
                return response(null, 400);
            }
            catch(\Exception $e) {
                Log::debug($e->getMessage());
                return response(null, 500);
            }
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
