<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', 'now');
        $arrivalTime = fake()->dateTimeBetween(
            $date->format('Y-m-d') . ' 07:00:00',
            $date->format('Y-m-d') . ' 09:00:00'
        );
        $departureTime = fake()->optional(0.8)->dateTimeBetween(
            $date->format('Y-m-d') . ' 16:00:00',
            $date->format('Y-m-d') . ' 19:00:00'
        );

        return [
            'employee_id' => Employee::factory(),
            'date' => $date->format('Y-m-d'),
            'arrival_time' => $arrivalTime,
            'departure_time' => $departureTime,
        ];
    }

    public function checkedIn(): static
    {
        return $this->state(fn(array $attributes) => [
            'departure_time' => null,
        ]);
    }

    public function checkedOut(): static
    {
        return $this->state(function (array $attributes) {
            $date = $attributes['date'] ?? now()->toDateString();
            return [
                'departure_time' => fake()->dateTimeBetween(
                    $date . ' 16:00:00',
                    $date . ' 19:00:00'
                ),
            ];
        });
    }
}
