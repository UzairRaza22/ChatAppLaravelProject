<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class MessageSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentication is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:1', 'max:255'],
            'channel_id' => ['nullable', 'string', 'regex:/^[a-f\d]{24}$/i'], // MongoDB ObjectId
            'workspace_id' => ['nullable', 'string', 'regex:/^[a-f\d]{24}$/i'], // MongoDB ObjectId
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.required' => 'A search query is required.',
            'query.min' => 'Query must be at least 1 character.',
            'query.max' => 'Query cannot exceed 255 characters.',
            'channel_id.regex' => 'channel_id must be a valid MongoDB ObjectId.',
            'workspace_id.regex' => 'workspace_id must be a valid MongoDB ObjectId.',
            'per_page.min' => 'per_page must be at least 1.',
            'per_page.max' => 'per_page cannot exceed 100.',
            'page.min' => 'page must be at least 1.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => $this->input('per_page') ? (int) $this->input('per_page') : 20,
            'page' => $this->input('page') ? (int) $this->input('page') : 1,
        ]);
    }

    /**
     * Get validated search parameters.
     */
    public function getSearchParams(): array
    {
        return [
            'query' => $this->validated('query'),
            'channel_id' => $this->validated('channel_id'),
            'per_page' => $this->validated('per_page', 15),
            'page' => $this->validated('page', 1),
        ];
    }
}
