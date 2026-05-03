<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'department_id', 'company_id',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}