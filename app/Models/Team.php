<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    public static function getAll() : Collection
    {
        return Team::all();
    }

    public function getTeam($id) : Team 
    {
        return Team::findOrFail($id);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
