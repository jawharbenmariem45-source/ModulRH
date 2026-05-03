<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
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