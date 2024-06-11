<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeaveRequest $request, Leave $leave)
    {
        $team_id = $request->team_id;

        try{
            $team_requests = Leave::getTeamLeaves($team_id);
            $availableDates = false;
            foreach($team_requests as $team_request) {
                if($request->date_from > $team_request->date_to ) {
                    $availableDates = true;
                }
            }

            if($availableDates) {
                $leave->date_from = $request->date_from;
                $leave->date_to = $request->date_to;
                $leave->user_id = $request->user_id;
                $leave->leave_type_id = $request->leave_type_id;
                
                $leave->save();
                return response()->json('Request for leave created!', 201);                
            }
            else{
                return response()->json('Leave dates cannot overlap inside team!', 422);
            }
            
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
    public function show(string $id)
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

    public function getTeamLeaves(int $team_id)
    {
        try{
            $vacations = Leave::getTeamLeaves($team_id);
            return response()->json($vacations);
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
