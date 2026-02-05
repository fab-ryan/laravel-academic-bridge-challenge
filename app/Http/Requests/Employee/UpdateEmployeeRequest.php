<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'names' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
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
            'phone_number' => ['sometimes', 'required', 'string', 'max:20'],
        ];
    }
}
