<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $status = $this->input('status');
        $confirmationRules = in_array($status, [ApplicationStatus::Accepted->value, ApplicationStatus::Rejected->value], true)
            ? ['required', 'accepted']
            : ['nullable'];

        return [
            'status' => ['required', Rule::in(ApplicationStatus::values())],
            'admin_response' => ['nullable', 'string', 'max:2500'],
            'confirmed' => $confirmationRules,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'admin_response' => is_string($this->input('admin_response')) ? trim(strip_tags($this->input('admin_response'))) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Selecciona el estado que quieres aplicar.',
            'status.in' => 'El estado seleccionado no es valido.',
            'admin_response.max' => 'La respuesta no puede superar los 2500 caracteres.',
            'confirmed.required' => 'Confirma la decision antes de aceptar o rechazar.',
            'confirmed.accepted' => 'Confirma la decision antes de aceptar o rechazar.',
        ];
    }
}
