<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'name', 'description'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'department_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employers()
    {
        return $this->users();
    }
}