<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_can_list_employees(): void
    {
        Employee::factory()->count(5)->create();

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                ],
            ]);
    }

    public function test_can_create_employee(): void
    {
        $response = $this->postJson('/api/employees', [
            'names' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+250788123456',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Employee created successfully',
            ]);

        $this->assertDatabaseHas('employees', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_show_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $employee->id,
                    'names' => $employee->names,
                ],
            ]);
    }

    public function test_can_update_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'names' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Employee updated successfully',
            ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'names' => 'Updated Name',
        ]);
    }

    public function test_can_delete_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ]);

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }

    public function test_can_search_employees(): void
    {
        Employee::factory()->create(['names' => 'John Doe']);
        Employee::factory()->create(['names' => 'Jane Smith']);

        $response = $this->getJson('/api/employees?search=John');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    public function test_cannot_create_employee_with_duplicate_email(): void
    {
        Employee::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/employees', [
            'names' => 'New Employee',
            'email' => 'existing@example.com',
            'phone_number' => '+250788123456',
        ]);

        $response->assertStatus(422);
    }
}
