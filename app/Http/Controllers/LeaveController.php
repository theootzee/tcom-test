<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RespondLeaveRequest;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            $alreadyRequested = [];
            foreach($team_requests as $team_request) {
                if($request->date_from < $team_request->date_to) {
                    array_push($alreadyRequested, $team_request->date_to);
                }
            }
            
            if(count($alreadyRequested) == 0) {
                
                $days = parent::calculateLeaveDays($request->date_from, $request->date_to);

                $user_leave_info = User::getDays($request->leave_type_id, $request->user_id);
                
                $enough_days = false;
                
                //check which leave type user has requested
                if($user_leave_info->leave_type_id == 1) {
                     $days <= $user_leave_info->vacation_days ? $enough_days = true : $enough_days = false;
                }
                else{
                    $days <= $user_leave_info->free_days ? $enough_days = true : $enough_days = false;
                }

                //store request if there is enough days left
                if($enough_days) {
                    $leave->date_from = $request->date_from;
                    $leave->date_to = $request->date_to;
                    $leave->user_id = $request->user_id;
                    $leave->leave_type_id = $request->leave_type_id;

                    $leave->save();
                    return response()->json('Request for leave created!', 201);     
                }
                else{
                    return response()->json('Not enough days left', 422);
                }
                           
            }
            else{
                return response()->json('Leave dates cannot overlap!', 422);
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
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try{
            $leave = Leave::getLeave($id);

            if($leave->accepted == 0) 
            {
                Leave::cancel($id);
            }

            return response()->json('Leave request has been canceled');
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

    public function respond(RespondLeaveRequest $request, int $id) 
    {
        try{
            $manager_id = $request->mng_id;
            $is_accepted = $request->is_accepted;

            DB::transaction(function () use($manager_id, $is_accepted, $id) {
                Leave::respond($id, $manager_id, $is_accepted);
                
                if($is_accepted == 1) {
                    $user = Leave::getLeave($id);
                    $days = parent::calculateLeaveDays($user->date_from, $user->date_to);
                    User::decrementLeaveDays($user->user_id, $user->leave_type_id, $days);
                }
            });

            return response()->json('Succesfully responded on leave request.', 204);
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
