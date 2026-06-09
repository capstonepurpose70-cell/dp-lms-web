<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guarded by auth:admin middleware
    }

    public function rules(): array
    {
        return [
            // Account
            'name'          => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.]+$/'],
            'email'         => ['required', 'email', 'unique:users,email'],
           'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'contact_number'=> ['nullable', 'string', 'max:11'],
            'civil_status'  => ['nullable', 'in:single,married,widowed,separated'],

            // Professional
            'department'         => ['required', 'string'],
            'position'           => ['nullable', 'string'],
            'date_hired'         => ['nullable', 'date'],
            'education'          => ['nullable', 'string'],
            'years_experience'   => ['nullable', 'integer', 'min:0', 'max:50'],
            'specializations_json'=> ['nullable', 'json'],
            'certifications'     => ['nullable', 'string', 'max:500'],

            // Assignment
            'section_id'         => ['nullable', 'exists:sections,id'],
            'is_adviser'         => ['nullable', 'boolean'],
            'adviser_section_id' => ['nullable', 'exists:sections,id'],
            'subjects'           => ['nullable', 'array'],
            'subjects.*'         => ['exists:subjects,id'],

            // NOTE: no password rules — sent via invite email
        ];   
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name must contain letters, spaces, and periods only.',
            'email.unique'=> 'An account with this email already exists.',
        ];
    }
}