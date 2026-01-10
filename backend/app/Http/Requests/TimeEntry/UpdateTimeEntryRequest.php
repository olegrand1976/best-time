<?php

declare(strict_types=1);

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimeEntryRequest extends FormRequest
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
        return [
            'project_id' => ['sometimes', 'nullable', 'integer', Rule::exists('projects', 'id')],
            'start_time' => ['sometimes', 'required', 'date'],
            'end_time' => ['sometimes', 'nullable', 'date', 'after:start_time'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
