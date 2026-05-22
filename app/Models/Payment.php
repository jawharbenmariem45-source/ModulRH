<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'contract_type',
        'base_salary',
        'overtime_hours',
        'overtime_amount',
        'bonuses',
        'allowances',
        'gross_salary',
        'cnss',
        'irpp',
        'css',
        'amount',
        'launch_date',
        'done_time',
        'month',
        'year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias compatibilité
    public function employer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}