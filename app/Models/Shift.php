<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'starts_at',
        'ends_at',
        'pause_start',
        'pause_end',
        'work_days',
        'is_default',
        'actif',
    ];

    protected $casts = [
        'work_days'  => 'array',
        'is_default' => 'boolean',
        'actif'      => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'shift_user');
    }
}