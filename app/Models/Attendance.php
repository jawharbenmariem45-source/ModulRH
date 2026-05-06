<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'date',
        'check_in_morning_time',
        'check_out_morning_time',
        'check_in_afternoon_time',
        'check_out_afternoon_time',
        'status',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}