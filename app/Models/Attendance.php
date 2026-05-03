<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'check_in_morning_time'    => 'datetime',
        'check_out_morning_time'   => 'datetime',
        'check_in_afternoon_time'  => 'datetime',
        'check_out_afternoon_time' => 'datetime',
        'date'                     => 'date',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}