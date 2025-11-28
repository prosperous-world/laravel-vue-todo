<?php

namespace App\Http\Requests\Todo;

use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'completed' => ['boolean'],
            'due_date' => ['nullable', 'date'],
            'tags' => ['array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}

