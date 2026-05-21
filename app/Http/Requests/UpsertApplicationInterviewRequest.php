<?php

namespace App\Http\Requests;

use App\Models\ApplicationInterview;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertApplicationInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date'],
            'interviewer_id' => ['nullable', Rule::exists(User::class, 'id')],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([
                ApplicationInterview::STATUS_SCHEDULED,
                ApplicationInterview::STATUS_COMPLETED,
                ApplicationInterview::STATUS_CANCELLED,
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'result_notes' => ['nullable', 'string', 'max:2500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location' => $this->cleanText('location'),
            'notes' => $this->cleanText('notes'),
            'result_notes' => $this->cleanText('result_notes'),
            'status' => $this->input('status') ?: ApplicationInterview::STATUS_SCHEDULED,
        ]);
    }

    public function messages(): array
    {
        return [
            'scheduled_at.required' => 'Indica la fecha y hora de la entrevista.',
            'scheduled_at.date' => 'La fecha de entrevista no es valida.',
            'interviewer_id.exists' => 'El entrevistador seleccionado no existe.',
            'status.in' => 'El estado de la entrevista no es valido.',
        ];
    }

    private function cleanText(string $key): ?string
    {
        $value = trim(strip_tags((string) $this->input($key, '')));

        return $value !== '' ? $value : null;
    }
}
