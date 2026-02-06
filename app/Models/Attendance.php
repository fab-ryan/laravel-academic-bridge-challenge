<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance Model
 *
 * @property string $id
 * @property string $employee_id
 * @property \Illuminate\Support\Carbon $arrival_time
 * @property \Illuminate\Support\Carbon|null $departure_time
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 */
class Attendance extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'arrival_time',
        'departure_time',
        'date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'arrival_time' => 'datetime',
            'departure_time' => 'datetime',
            'date' => 'date',
        ];
    }

    /**
     * Get the employee that owns the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if the employee has checked out.
     */
    public function hasCheckedOut(): bool
    {
        return $this->departure_time !== null;
    }

    /**
     * Calculate hours worked.
     */
    public function getHoursWorkedAttribute(): ?string
    {
        if (!$this->departure_time || !$this->arrival_time) {
            return null;
        }

        $diff = $this->departure_time->diff($this->arrival_time);
        return $diff->format('%H:%I:%S');
    }
}
