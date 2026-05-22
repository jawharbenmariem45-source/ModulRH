<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'details', 'duration_days', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'employer_contract', 'contract_id', 'user_id')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    // Alias compatibilité
    public function employers()
    {
        return $this->users();
    }
}