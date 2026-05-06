<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference',
        'employer_id',
        'amount',
        'launch_date',
        'done_time',
        'status',
        'month',
        'year',
        'type_contrat',
        'salaire_base',
        'heures_sup',
        'montant_heures_sup',
        'primes',
        'indemnites',
        'salaire_brut',
        'cnss',
        'irpp',
        'css',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }
    
}