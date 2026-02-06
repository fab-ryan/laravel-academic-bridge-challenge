<?php

namespace App\Enums;

/**
 * Attendance Type Enum
 *
 * Represents the type of attendance action.
 */
enum AttendanceType: string
{
    case CHECK_IN = 'check-in';
    case CHECK_OUT = 'check-out';

    /**
     * Get the display label for the attendance type.
     */
    public function label(): string
    {
        return match ($this) {
            self::CHECK_IN => 'Check-In',
            self::CHECK_OUT => 'Check-Out',
        };
    }

    /**
     * Get the description for the attendance type.
     */
    public function description(): string
    {
        return match ($this) {
            self::CHECK_IN => 'Employee arrival time recorded',
            self::CHECK_OUT => 'Employee departure time recorded',
        };
    }
}
