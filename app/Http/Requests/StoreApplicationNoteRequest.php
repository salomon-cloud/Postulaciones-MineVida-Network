<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isReviewer() ?? false;
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => is_string($this->input('note')) ? trim(strip_tags($this->input('note'))) : null,
        ]);
    }
}
