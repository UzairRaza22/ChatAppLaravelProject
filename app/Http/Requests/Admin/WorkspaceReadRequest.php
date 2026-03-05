<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Workspace;

class WorkspaceReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => ['nullable', 'exists:workspaces,_id'],
        ];
    }

    public function validatedWorkspace()
    {
        return $this->filled('workspace_id')
            ? Workspace::findOrFail($this->workspace_id)
            : Workspace::all();
    }
}
