<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Leave extends Model
{
    use HasFactory;

    public static function getTeamLeaves($team_id) : Collection
    {
        return DB::table('users as u') 
        ->join('teams as t', 'u.team_id', '=', 't.id')
        ->join('leaves as l', 'u.id', '=', 'l.user_id')
        ->join('leave_types as lt', 'l.leave_type_id', '=', 'lt.id')
        ->where([
            't.id' => $team_id
        ])->get(["firstname", "lastname", "team_name", "date_from", "date_to", "responded" ,"accepted", "lt.type"]);
    }    
}
