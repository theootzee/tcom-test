<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Mail\TestMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $users = User::getAll();
            return response()->json($users);
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
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, User $user)
    {
        $user->email = $request->email;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $password = Str::password(12,true,true,false,false);
        $user->password = sha1($password);
        $user->role_id = $request->role_id;

        //if user has admin rights than he doesn't belong to any of teams
        $request->role_id != 1 ? $user->team_id = $request->team_id : $user->team_id = null;

        //only employee an manager can have holidays and free days
        $request->role_id != 1 ? $user->free_days = 5 : $user->free_days = null;
        $request->role_id != 1 ? $user->vacation_days = 20 : $user->vacation_days = null;

        try{
            $user->save();

            Mail::to($user->email)->send(new TestMail([
                'title' => 'Registration',
                'body' => "Your password is: $password"
            ]));

            if($user->role_id == 2) {
                $teams = $request->teams;
                foreach ($teams as $team) {
                    $user->teams()->attach($team);
                }
            }
            
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
            
            $user = User::getOneUser($id);

            if(!$user) 
            {
                return response('User does not exist!', 404);
            }

            if($user->role_id == 1) {
                $result = User::checkAdminsCount();
                if($result == 1) {
                    return response('Can not delete last admin', 409);
                }
            }

            if($user->role_id == 2) {
                $result = User::checkManagersCountForTeam($user->team_id);
                if($result == 1) {
                    return response('Can not delete only manager of team', 409);
                }
            }

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
