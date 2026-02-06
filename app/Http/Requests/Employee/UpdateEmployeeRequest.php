<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'names' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'employee_identifier' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'phone_number' => ['sometimes', 'required', 'string', 'regex:/^\+?[0-9]{10,15}$/', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'names.required' => 'Employee name is required.',
            'names.min' => 'Employee name must be at least 2 characters long.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered to another employee.',
            'employee_identifier.unique' => 'This employee identifier is already in use.',
            'phone_number.regex' => 'Please provide a valid phone number (10-15 digits, optionally starting with +).',
        ];
    }
}
