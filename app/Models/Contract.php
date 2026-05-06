<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'details',
        'duration_days',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function employers()
    {
        return $this->belongsToMany(Employer::class, 'employer_contract')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }
}