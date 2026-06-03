<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractType extends Model
{
    use HasFactory;

    protected $table = 'contract_types';

    protected $fillable = [
        'name', 'details', 'duration_days', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}