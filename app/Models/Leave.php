<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Leave extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mng_id',
        'accepted',
        'user_id',
        'date_from',
        'date_to',
        'leave_type_id'
    ];

    public static function getTeamLeaves($team_id) : Collection
    {
        return DB::table('users as u') 
        ->join('teams as t', 'u.team_id', '=', 't.id')
        ->join('leaves as l', 'u.id', '=', 'l.user_id')
        ->join('leave_types as lt', 'l.leave_type_id', '=', 'lt.id')
        ->where([
            't.id' => $team_id
        ])->get(["firstname", "lastname", "team_name", "date_from", "date_to",  "accepted", "lt.type"]);
    }    

    public static function respond($id, $manager_id, $is_accepted) 
    {   
        Leave::find($id)
        ->update([
            "mng_id" => $manager_id,
            "accepted" => $is_accepted
        ]);
    }

    public static function getLeave($id) 
    {
        return Leave::find($id);
    }

    public static function cancel($id) 
    {
        Leave::destroy($id);
    }
}
