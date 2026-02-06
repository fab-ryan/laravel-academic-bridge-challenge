<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create sample employees
        $employees = Employee::factory(10)->create();

        // Create attendance records for each employee
        foreach ($employees as $employee) {
            // Create attendance records for the last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();

                // 80% chance of having attendance for each day
                if (fake()->boolean(80)) {
                    $arrivalHour = fake()->numberBetween(7, 9);
                    $arrivalMinute = fake()->numberBetween(0, 59);
                    $arrivalTime = now()->subDays($i)->setTime($arrivalHour, $arrivalMinute);

                    $departureTime = null;
                    // 70% chance of having checked out (except for today)
                    if ($i > 0 || fake()->boolean(70)) {
                        $departureHour = fake()->numberBetween(16, 19);
                        $departureMinute = fake()->numberBetween(0, 59);
                        $departureTime = now()->subDays($i)->setTime($departureHour, $departureMinute);
                    }

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'arrival_time' => $arrivalTime,
                        'departure_time' => $departureTime,
                    ]);
                }
            }
        }
    }
}
