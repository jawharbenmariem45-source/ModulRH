<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Employer extends Authenticatable
{
    use HasFactory, HasRoles;

    protected $guard_name = 'employer'; 

    protected $fillable = [
    'department_id', 'company_id', 'nom', 'prenom', 'email', 'password', 
    'numero_telephone', 'type_contrat', 'date_debut', 'date_fin', 
    'rib', 'cnss', 'salaire', 'rib_image',
    'chef_famille', 'nombre_enfants', 'nombre_enfants_infirmes', 'nombre_enfants_etudiants'
];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'department_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }
    public function contracts()
{
    return $this->belongsToMany(Contract::class, 'employer_contract')
        ->withPivot('start_date', 'end_date')
        ->withTimestamps();
}

public function activeContract()
{
    return $this->contracts()->latest('employer_contract.created_at')->first();
}
public function attendances()
{
    return $this->hasMany(Attendance::class);
}
}