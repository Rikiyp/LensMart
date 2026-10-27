<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['verify', 'reject'])],
            'rejection_note' => ['nullable', 'required_if:action,reject', 'string', 'max:1000'],
        ];
    }
}
