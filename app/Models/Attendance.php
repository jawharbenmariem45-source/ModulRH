<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'morning_check_in',
        'morning_check_out',
        'afternoon_check_in',
        'afternoon_check_out',
        'status',
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