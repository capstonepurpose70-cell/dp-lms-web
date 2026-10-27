<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Single "Add Teacher". Only fields that are actually STORED are asked for.
 * No password: the teacher sets it through their activation link.
 */
class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guarded by auth:admin middleware
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'           => preg_replace('/\s+/', ' ', trim((string) $this->name)),
            'email'          => $this->email ? strtolower(trim($this->email)) : null,
            'employee_id'    => $this->employee_id ? trim($this->employee_id) : null,
            'contact_number' => $this->contact_number ? preg_replace('/[\s\-]/', '', $this->contact_number) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'\-]+$/u'],
            'email'              => ['nullable', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'employee_id'        => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')],
            // users.contact_number has a UNIQUE index (it is also a login identifier)
            'contact_number'     => ['nullable', 'regex:/^09\d{9}$/', Rule::unique('users', 'contact_number')],
            'adviser_section_id' => ['nullable', Rule::exists('sections', 'id')->where('is_active', true)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex'                => 'Name may only contain letters, spaces, periods, apostrophes and hyphens.',
            'email.unique'              => 'An account with this email already exists.',
            'employee_id.unique'        => 'This employee ID is already used by another account.',
            'contact_number.regex'      => 'Mobile number must be 11 digits starting with 09, e.g. 09171234567.',
            'contact_number.unique'     => 'This mobile number is already used by another account.',
            'adviser_section_id.exists' => 'Please choose an active section.',
        ];
    }
}
