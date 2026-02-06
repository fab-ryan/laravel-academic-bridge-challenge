<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AttendanceRecordedNotification;
use Database\Factories\AttendanceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
        AttendanceFactory::resetSequence();
    }

    public function test_can_list_attendances(): void
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->count(3)->create(['employee_id' => $employee->id]);

        $response = $this->getJson('/api/v1/attendances');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                ],
            ]);
    }

    public function test_employee_can_check_in(): void
    {
        Notification::fake();
        $employee = Employee::factory()->create();

        $response = $this->postJson('/api/v1/attendances/check-in', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Check-in recorded successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        Notification::assertSentTo($employee, AttendanceRecordedNotification::class);
    }

    public function test_employee_cannot_check_in_twice_same_day(): void
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/v1/attendances/check-in', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Employee has already checked in today',
            ]);
    }

    public function test_employee_can_check_out(): void
    {
        Notification::fake();
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'departure_time' => null,
        ]);

        $response = $this->postJson('/api/v1/attendances/check-out', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Check-out recorded successfully',
            ]);

        $attendance->refresh();
        $this->assertNotNull($attendance->departure_time);

        Notification::assertSentTo($employee, AttendanceRecordedNotification::class);
    }

    public function test_employee_cannot_check_out_without_check_in(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->postJson('/api/v1/attendances/check-out', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'No check-in record found for today',
            ]);
    }

    public function test_can_get_today_attendance(): void
    {
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this->getJson("/api/v1/attendances/employee/{$employee->id}/today");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $attendance->id,
                ],
            ]);
    }

    public function test_can_filter_attendances_by_date(): void
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => now()->subDay()->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/attendances?date=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    public function test_can_filter_attendances_by_employee(): void
    {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        Attendance::factory()->create(['employee_id' => $employee1->id]);
        Attendance::factory()->create(['employee_id' => $employee2->id]);

        $response = $this->getJson("/api/v1/attendances?employee_id={$employee1->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }
}
