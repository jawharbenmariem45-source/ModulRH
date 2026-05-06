<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function configurations()
    {
        return $this->hasMany(Configuration::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}