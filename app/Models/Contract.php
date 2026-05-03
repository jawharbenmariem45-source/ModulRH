<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
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