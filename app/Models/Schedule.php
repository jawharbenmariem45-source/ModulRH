<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_time', 'end_time', 'break_start', 'break_end'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employers()
    {
        return $this->users();
    }
}