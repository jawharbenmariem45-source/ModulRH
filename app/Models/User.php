<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'email',
        'password',
        'phone',
        'gender',
        'departement_id',
        'company_id',
        'poste_id',
        'shift_id',
        'salary',
        'discipline_score',
        'family_head',
        'children_count',
        'disabled_children_count',
        'student_children_count',
        'contract_type',
        'rib',
        'rib_image',
        'cnss',
        'start_date',
        'end_date',
        'solde_conges',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'family_head'       => 'boolean',
        'start_date'        => 'date',
        'end_date'          => 'date',
    ];

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (User $user) {
            if (!$user->contract_type) return;
            $contractType = ContractType::where('name', $user->contract_type)->where('active', true)->first();
            if ($contractType) {
                Contract::create([
                    'user_id'          => $user->id,
                    'contract_type_id' => $contractType->id,
                    'start_date'       => $user->start_date,
                    'end_date'         => $user->end_date,
                ]);
            }
        });

        static::updated(function (User $user) {
            if (!$user->wasChanged(['contract_type', 'start_date', 'end_date'])) return;
            $contractType = ContractType::where('name', $user->contract_type)->where('active', true)->first();
            if ($contractType) {
                Contract::create([
                    'user_id'          => $user->id,
                    'contract_type_id' => $contractType->id,
                    'start_date'       => $user->start_date,
                    'end_date'         => $user->end_date,
                ]);
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class)->orderByDesc('created_at');
    }

    public function activeContract()
    {
        return $this->contracts()->whereNull('end_date')->orWhere('end_date', '>=', now())->first();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function conges()
    {
        return $this->leaves();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function isEmployer(): bool
    {
        return $this->hasRole('employer');
    }
}