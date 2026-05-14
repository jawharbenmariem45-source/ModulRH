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
        'department_id', 'company_id',
        'last_name', 'first_name',
        'email', 'password',
        'phone',
        'contract_type', 'start_date', 'end_date',
        'rib', 'cnss', 'salary', 'rib_image',
        'family_head',
        'children_count', 'disabled_children_count', 'student_children_count',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Employer $employer) {
            if (!$employer->contract_type) return;
            $contract = Contract::where('name', $employer->contract_type)->where('active', true)->first();
            if ($contract) {
                $employer->contracts()->attach($contract->id, [
                    'start_date' => $employer->start_date,
                    'end_date'   => $employer->end_date,
                ]);
            }
        });

        static::updated(function (Employer $employer) {
            if (!$employer->wasChanged(['contract_type', 'start_date', 'end_date'])) return;
            $contract = Contract::where('name', $employer->contract_type)->where('active', true)->first();
            if ($contract) {
                $employer->contracts()->attach($contract->id, [
                    'start_date' => $employer->start_date,
                    'end_date'   => $employer->end_date,
                ]);
            }
        });
    }

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
                    ->withTimestamps()
                    ->orderBy('employer_contract.created_at', 'desc');
    }

    public function activeContract()
    {
        return $this->contracts()->first();
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