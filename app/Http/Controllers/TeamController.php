<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Models\Team;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::getAll();
        return response()->json($teams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request, Team $team) 
    {
        $team->name = $request->name;
        $managers = $request->managers;

        try{
            $team->save();
            if($managers != []) {
               foreach($managers as $manager) {
                    $team->users()->attach($manager);
                }
            }
            return response()->json(["Team created!"], 201 );
        }
        catch(UniqueConstraintViolationException $e) {
            Log::critical($e->getMessage());
            return response("Team name already exists", 422);
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
    public function show(int $id, Team $team)
    {
        try{
            return $team->getTeam($id);
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
