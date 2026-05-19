<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'employees';

    // ✅ FIX: include position
    protected $fillable = [
        'name',
        'email',
        'pin_code',
        'position'
    ];

    protected $hidden = [
        'pin_code',
        'remember_token',
    ];

    // ❌ IMPORTANT FIX: use ID (NOT name)
    public function getRouteKeyName()
    {
        return 'id';
    }

    // =====================
    // RELATIONSHIPS
    // =====================

    public function check()
    {
        return $this->hasMany(Check::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function latetime()
    {
        return $this->hasMany(Latetime::class);
    }

    public function leave()
    {
        return $this->hasMany(Leave::class);
    }

    public function overtime()
    {
        return $this->hasMany(Overtime::class);
    }

    public function schedules()
    {
        return $this->belongsToMany(
            'App\Models\Schedule',
            'schedule_employees',
            'emp_id',
            'schedule_id'
        );
    }
}