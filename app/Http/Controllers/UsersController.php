<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $user->email = $request->email;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->password = "Test12345";
        $user->role_id = $request->role_id;

        //if user has admin rights than he doesn't belong to any of teams
        $request->role_id != 1 ? $user->team_id = $request->team_id : $user->team_id = null;

        //only employee can have holidays and free days
        $request->role_id != 1 ? $user->free_days = 5 : $user->free_days = null;
        $request->role_id != 1 ? $user->vacation_days = 20 : $user->vacation_days = null;

        try{
            $user->save();
            if($user->role_id == 2) {
                $teams = $request->teams;
                foreach ($teams as $team) {
                    $user->teams()->attach($team);
                }
            }
            
            #$token = $user->createToken('my-app-token', [''])->plainTextToken;
            #User::saveToken($token, $user->id);
            return response()->json([
            "message" => 'User created!', 
            "user" => $user
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

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try{
            $user = User::getOneUser($id);
            return response()->json($user, 200);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $email = $request->email;
        $firstname = $request->firstname;
        $lastname = $request->lastname;

        $role_id = $request->role_id;

        //if user has admin rights than he doesn't belong to any of teams
        $request->role_id != 1 ? $team_id = $request->team_id : $team_id = null;
        
        try{
           
            $user = User::with('teams')->find($id);

            if($user->email == $email) {
                $user->update([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'role_id' => $role_id,
                    'team_id' => $team_id
                ]);
            }
            else{
                $user->update([
                    "email" => $email,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'role_id' => $role_id,
                    'team_id' => $team_id
                ]);
            }

            foreach($user->teams as $item) {
                $user->teams()->detach($item->id);
            }

            if($user->role_id == 2) {
                $teams = $request->teams;
                foreach ($teams as $team) {
                    $user->teams()->attach($team);
                }
            }
           
            return response()->json(null, 204);
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try{
            User::deleteUser($id);
            return response("User deleted!", 204);
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
