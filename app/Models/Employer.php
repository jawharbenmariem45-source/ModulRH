<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class Employer extends Authenticatable
{
    use HasFactory, HasRoles;

    protected $guard_name = 'employer';

    protected $fillable = [
        'department_id', 'company_id', 'nom', 'prenom', 'email', 'password',
        'numero_telephone', 'type_contrat', 'date_debut', 'date_fin',
        'rib', 'cnss', 'salaire', 'rib_image',
        'chef_famille', 'nombre_enfants', 'nombre_enfants_infirmes', 'nombre_enfants_etudiants',
    ];

    // ══════════════════════════════════════════════
    // BOOT — Assignation automatique du contrat
    // ══════════════════════════════════════════════
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Employer $employer) {
            $contract = Contract::where('name', $employer->type_contrat)
                                ->where('active', true)
                                ->first();
            if ($contract) {
                $employer->contracts()->attach($contract->id, [
                    'start_date' => $employer->date_debut,
                    'end_date'   => $employer->date_fin,
                ]);
            }
        });

        static::updated(function (Employer $employer) {
            if ($employer->wasChanged(['type_contrat', 'date_debut', 'date_fin'])) {
                $contract = Contract::where('name', $employer->type_contrat)
                                    ->where('active', true)
                                    ->first();
                if ($contract) {
                    $employer->contracts()->sync([
                        $contract->id => [
                            'start_date' => $employer->date_debut,
                            'end_date'   => $employer->date_fin,
                        ],
                    ]);
                }
            }
        });
    }

    // ══════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════
    public function departement()
    {
        return $this->belongsTo(Departement::class, 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'employer_contract')
                    ->withPivot('start_date', 'end_date')
                    ->withTimestamps();
    }

    public function activeContract()
    {
        return $this->contracts()
                    ->latest('employer_contract.created_at')
                    ->first();
    }

    public function salaires() // ✅ ajouté
    {
        return $this->hasMany(Salaire::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}