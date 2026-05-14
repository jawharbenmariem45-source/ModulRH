<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;

    protected $table = 'leaves';

    protected $fillable = [
        'employer_id', 'type', 'start_date', 'end_date',
        'days_count', 'reason', 'document', 'status', 'comment',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }
}