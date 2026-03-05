<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImpersonateStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can stop impersonation
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            // No extra input needed
        ];
    }
}
