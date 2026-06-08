<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'pointage_at',
        'shift_user_id',
        'face_matched',
        'tx_hash',
        'block_number',
        'blockchain_statut',
        'device_ref',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shiftUser()
    {
        return $this->belongsTo(ShiftUser::class, 'shift_user_id');
    }
}