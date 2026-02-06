<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'names' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'employee_identifier' => 'EMP' . fake()->unique()->numerify('###'),
            'phone_number' => fake()->phoneNumber(),
        ];
    }
}
