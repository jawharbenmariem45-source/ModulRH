<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;

    
    protected $fillable = [
    'employer_id', 
    'type', 
    'date_debut', 
    'date_fin', 
    'statut', 
    'commentaire',
    'motif',        
    'nombre_jours', 
];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }
}