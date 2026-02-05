<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Employee extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'names',
        'email',
        'employee_identifier',
        'phone_number',
    ];

    // public function attendances(): HasMany
    // {
    //     return $this->hasMany(Attendance::class);
    // }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

}
