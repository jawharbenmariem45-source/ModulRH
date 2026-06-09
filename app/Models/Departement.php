<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'company_id', 'manager_id'];

    public function postes()
    {
        return $this->hasMany(Poste::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'departement_id');
    }

    public function employers()
    {
        return $this->users();
    }
}