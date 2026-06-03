<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function postes()
    {
        return $this->hasMany(Poste::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'departement_id');
    }

    // Alias compatibilité
    public function employers()
    {
        return $this->users();
    }
}
