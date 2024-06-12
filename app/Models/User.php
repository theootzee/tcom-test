<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role_id',
        'team_id',
        'vacation_days',
        'free_days'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    public static function login($email, $password) : User 
    {

        return  User::where([
            'email' => $email,
            'password' => sha1($password)
            ])->firstOrFail();

    }

    public static function getAll() : Collection
    {
        return User::all();
    }

    public function teams() 
    {
        return $this->belongsToMany(Team::class);
    }

    public static function saveToken($token, $id) {
        User::where('id', $id)->update(['remember_token' => $token]);
    }

    public static function deleteUser($id) : void
    {
        User::destroy($id);
    }

    public static function getOneUser($id) 
    {
        return User::find($id);
    }

    public static function getDays($user_id) 
    {
        return DB::table('users as u')
        ->where([
            "u.id" => $user_id,
        ])
        ->get(['free_days', 'vacation_days'])
        ->first();
    }

    public static function decrementLeaveDays($user_id, $leave_type_id, $days) 
    {
        if($leave_type_id == 1) {
            User::find($user_id)->decrement('vacation_days', $days);
        }
        else{
            User::find($user_id)->decrement('free_days', $days);
        }
    }

    public static function checkAdminsCount() 
    {
        return User::where('role_id', '=', 1)->count();
    }

    public static function checkManagersCountForTeam($team_id) 
    {
        return 
        DB::table('users as u')
        ->join('team_user as tu', 'u.id', '=', 'tu.user_id')
        ->join('teams as t', 'tu.team_id', '=', 't.id')
        ->where([
            "t.id" => $team_id
        ])
        ->distinct()->count('tu.user_id');       
    }

    public static function getAllRequestedLeavesForUser($id) 
    {
        // $user = User::find($id)->leaves();
        // return $user;
        return DB::table('users as u')
        ->join('leaves as l', 'u.id', '=', 'l.user_id')
        ->join('leave_types as lt', 'l.leave_type_id', '=', 'lt.id')
        ->where([
            'u.id' => $id,
            'mng_id' => null
        ])
        ->get(["date_from", "date_to", "accepted"]);
    }

    public static function getAllRespondedLeavesForUser($id) 
    {
        return DB::table('users as u')
        ->join('leaves as l', 'u.id', '=', 'l.user_id')
        ->join('leave_types as lt', 'l.leave_type_id', '=', 'lt.id')
        ->join('users as manager', 'l.mng_id', '=', 'manager.id')
        ->where([
            'u.id' => $id
        ])
        ->get(["date_from", "date_to", "accepted", "manager.firstname as manager_firstname", "manager.lastname as manager lastname"]);
    }

    public static function updatePassword($password, $user_id) 
    {
        User::find($user_id)->update(['password' => $password]);
    }

    public static function changeForgotPassword($email, $password) 
    {
        User::where("email", $email)
        ->update([
            "password" => sha1($password)
        ]);
    }
    
}
