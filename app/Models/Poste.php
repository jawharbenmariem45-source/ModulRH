<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poste extends Model
{
    use HasFactory;

    protected $table = 'postes';

    protected $fillable = ['departement_id', 'name', 'description'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'poste_id');
    }

    // Alias compatibilité
    public function employers()
    {
        return $this->users();
    }
}
