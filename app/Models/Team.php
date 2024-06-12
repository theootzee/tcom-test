<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Team extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function getAll() : Collection
    {
        return Team::all();
    }

    public static function getTeam($id)
    {
        return Team::find($id);
    }

    //this relationship respresents managers for the team
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public static function updateTeam($id, $name, $managers) 
    {
        DB::transaction(function () use($managers, $name, $id) {
            $team = Team::with('users')->find($id);

            $team->team_name = $name;

            $team->save();

            
            //remove all managers for that team 
            foreach($team->users as $item) {
                $team->users()->detach($item->id);
            }

            //save newly entered managers for that team
            foreach ($managers as $manager) {
                $team->users()->attach($manager);
            }
        }); 
    }

    public static function checkTeamForDeletion($team_id) 
    {
        return DB::table('teams as t')
        ->join('users as u', 't.id', '=', 'u.team_id')
        ->where('t.id', $team_id)
        ->get(['u.id'])
        ->toArray();
    }

    public static function deleteTeam($id) 
    {
        Team::destroy($id);
    }
}
