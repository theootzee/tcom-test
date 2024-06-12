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

    public static function getOneUser($id) : User
    {
        return User::firstOrFail($id);
    }

    public static function getDays($leave_type_id, $user_id) 
    {
        return DB::table('users as u')
        ->join('leaves as l', 'u.id', '=', 'l.user_id')
        ->join('leave_types as lt', 'l.leave_type_id', '=', 'lt.id')
        ->where([
            "u.id" => $user_id,
            "lt.id" => $leave_type_id
        ])
        ->get(['type', 'free_days', 'vacation_days', 'lt.id as leave_type_id'])
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

}
