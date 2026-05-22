<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;

    protected $table = 'leaves';

    protected $fillable = [
        'user_id', 'type', 'start_date', 'end_date',
        'days_count', 'reason', 'document', 'status',
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