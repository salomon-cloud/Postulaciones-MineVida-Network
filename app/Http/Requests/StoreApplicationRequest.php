<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use App\Models\Setting;
use App\Support\ApplicationCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $category = is_string($type) ? ApplicationCatalog::category($type, true) : null;

        if (! $category && ApplicationCatalog::types(true) !== []) {
            return ['type' => ['required', 'in:'.implode(',', array_keys(ApplicationCatalog::types(true)))]];
        }

        if (! is_string($type) || ! isset(ApplicationCatalog::types(true)[$type])) {
            return ['type' => ['required']];
        }

        $minimumAge = ApplicationCatalog::minimumAge($type);

        return collect(ApplicationCatalog::fields($type, true))
            ->mapWithKeys(function (array $field, string $key) use ($minimumAge) {
                $rules = array_map(
                    fn (string $rule) => str_replace('{min_age}', (string) $minimumAge, $rule),
                    $field['rules'],
                );

                return [$key => $rules];
            })
            ->prepend(['required', 'in:'.implode(',', array_keys(ApplicationCatalog::types(true)))], 'type')
            ->all();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');

            if (! is_string($type) || ! isset(ApplicationCatalog::types(true)[$type])) {
                return;
            }

            if (! Setting::bool('applications_open', true)) {
                $validator->errors()->add('type', 'Las postulaciones estan cerradas por ahora.');
            }

            $category = ApplicationCatalog::category($type, true);

            if (! $category?->is_open) {
                $message = $category?->closed_message ?: 'Esta categoria no esta recibiendo postulaciones por ahora.';

                if ($category?->closed_until) {
                    $message .= ' Podras intentarlo despues de '.$category->closed_until->format('Y-m-d H:i').'.';
                }

                $validator->errors()->add('type', $message);
            }

            $activeStatuses = collect(ApplicationStatus::cases())
                ->filter(fn (ApplicationStatus $status) => $status->isActive())
                ->map->value
                ->all();

            $hasActiveApplication = $this->user()->applications()
                ->where('type', $type)
                ->whereIn('status', $activeStatuses)
                ->exists();

            if ($hasActiveApplication) {
                $validator->errors()->add('type', 'Ya tienes una postulacion activa de este tipo.');
            }

            $cooldown = $this->user()->applications()
                ->where('type', $type)
                ->where('status', ApplicationStatus::Rejected->value)
                ->where('cooldown_until', '>', now())
                ->latest('cooldown_until')
                ->first();

            if ($cooldown) {
                $validator->errors()->add('type', 'Debes esperar hasta '.$cooldown->cooldown_until->format('Y-m-d H:i').' para volver a postularte.');
            }

            foreach (ApplicationCatalog::fields($type, true) as $key => $field) {
                if (($field['type'] ?? null) === 'textarea_urls') {
                    $this->validateUrlList($validator, $key);
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitize($this->all()));
    }

    public function applicationData(): array
    {
        return $this->safe()->only([
            'type',
            'minecraft_nick',
            'age',
            'country',
            'timezone',
            'available_schedule',
        ]);
    }

    public function answerData(): array
    {
        $type = $this->validated('type');

        return collect(ApplicationCatalog::answerFields($type, true))
            ->mapWithKeys(function (array $field, string $key) {
                $answer = $this->validated($key);

                if (($field['type'] ?? null) === 'checkbox') {
                    $answer = $answer ? 'Aceptado' : 'No aceptado';
                }

                if (($field['type'] ?? null) === 'select') {
                    $answer = $field['options'][$answer] ?? $answer;
                }

                return [$field['label'] => (string) $answer];
            })
            ->all();
    }

    private function validateUrlList(Validator $validator, string $key): void
    {
        $value = (string) $this->input($key, '');
        $urls = collect(preg_split('/[\r\n,]+/', $value))
            ->map(fn (string $url) => trim($url))
            ->filter()
            ->all();

        foreach ($urls as $url) {
            if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                $validator->errors()->add($key, 'Cada link debe ser una URL valida con http o https.');
                break;
            }
        }
    }

    private function sanitize(array $data): array
    {
        return collect($data)->map(function ($value) {
            if (is_array($value)) {
                return $this->sanitize($value);
            }

            if (is_string($value)) {
                return trim(strip_tags($value));
            }

            return $value;
        })->all();
    }
}
