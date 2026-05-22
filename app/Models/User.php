<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'department_id',
        'company_id',
        'post_id',
        'schedule_id',
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
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'family_head'       => 'boolean',
    ];

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (User $user) {
            if (!$user->contract_type) return;
            $contract = Contract::where('name', $user->contract_type)->where('active', true)->first();
            if ($contract) {
                $user->contracts()->attach($contract->id, [
                    'start_date' => $user->start_date,
                    'end_date'   => $user->end_date,
                ]);
            }
        });

        static::updated(function (User $user) {
            if (!$user->wasChanged(['contract_type', 'start_date', 'end_date'])) return;
            $contract = Contract::where('name', $user->contract_type)->where('active', true)->first();
            if ($contract) {
                $user->contracts()->attach($contract->id, [
                    'start_date' => $user->start_date,
                    'end_date'   => $user->end_date,
                ]);
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'employer_contract', 'user_id', 'contract_id')
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
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function conges()
    {
        return $this->hasMany(Conge::class, 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
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