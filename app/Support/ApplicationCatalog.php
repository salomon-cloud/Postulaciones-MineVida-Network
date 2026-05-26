<?php

namespace App\Support;

use App\Models\ApplicationCategory;
use App\Models\ApplicationQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ApplicationCatalog
{
    public static function columnFields(): array
    {
        return [
            'minecraft_nick',
            'age',
            'country',
            'timezone',
            'available_schedule',
        ];
    }

    public static function types(bool $includeClosed = false): array
    {
        if (self::hasDynamicCatalog()) {
            return ApplicationCategory::query()
                ->when(! $includeClosed, fn ($query) => $query->where('is_open', true))
                ->ordered()
                ->pluck('name', 'slug')
                ->all();
        }

        return collect(self::defaultDefinitions())
            ->mapWithKeys(fn (array $definition, string $slug) => [$slug => $definition['label']])
            ->all();
    }

    public static function categories(bool $includeClosed = false): Collection
    {
        if (! self::hasDynamicCatalog()) {
            return collect();
        }

        return ApplicationCategory::query()
            ->with(['questions' => fn ($query) => $query->orderBy('step')->orderBy('sort_order')->orderBy('id')])
            ->when(! $includeClosed, fn ($query) => $query->where('is_open', true))
            ->ordered()
            ->get();
    }

    public static function category(string $type, bool $includeClosed = false): ?ApplicationCategory
    {
        if (! self::hasDynamicCatalog()) {
            return null;
        }

        return ApplicationCategory::query()
            ->with(['questions' => fn ($query) => $query->orderBy('step')->orderBy('sort_order')->orderBy('id')])
            ->where('slug', $type)
            ->when(! $includeClosed, fn ($query) => $query->where('is_open', true))
            ->first();
    }

    public static function definition(string $type, bool $includeClosed = false): array
    {
        if (self::hasDynamicCatalog()) {
            $category = self::category($type, $includeClosed);
            abort_unless($category, 404);

            return self::definitionFromCategory($category);
        }

        $defaults = self::defaultDefinitions();
        abort_unless(isset($defaults[$type]), 404);

        return self::definitionFromDefault($type, $defaults[$type]);
    }

    public static function formSteps(string $type, bool $includeClosed = false): array
    {
        return self::definition($type, $includeClosed)['steps'];
    }

    public static function fields(string $type, bool $includeClosed = false): array
    {
        return self::definition($type, $includeClosed)['fields'];
    }

    public static function answerFields(string $type, bool $includeClosed = false): array
    {
        return collect(self::fields($type, $includeClosed))
            ->filter(fn (array $field, string $key) => ($field['is_answer'] ?? ! in_array($key, self::columnFields(), true)) === true)
            ->all();
    }

    public static function minimumAge(string $type): int
    {
        $category = self::category($type, true);

        return $category?->minimum_age ?: \App\Models\Setting::integer('minimum_age', 15);
    }

    public static function defaultSteps(): array
    {
        return [
            [
                'title' => 'Datos generales',
                'description' => 'Confirma tu cuenta y completa la informacion basica para identificarte.',
            ],
            [
                'title' => 'Preguntas generales',
                'description' => 'Responde preguntas basicas sobre tu disponibilidad, experiencia y forma de trabajar.',
            ],
            [
                'title' => 'Preguntas del equipo',
                'description' => 'Responde situaciones y preguntas especificas de esta categoria.',
            ],
            [
                'title' => 'Enviar',
                'description' => 'Revisa que toda la informacion este correcta antes de enviar.',
            ],
        ];
    }

    public static function defaultDefinitions(): array
    {
        return [
            'staff' => [
                'label' => 'Staff',
                'summary' => 'Moderacion, soporte y convivencia dentro del servidor.',
                'description' => 'Buscamos personas comprometidas, activas y con buena actitud.',
                'icon' => 'ST',
                'accent_color' => '#facc15',
                'sort_order' => 10,
                'steps' => self::defaultSteps(),
                'fields' => array_merge(self::commonFields(), [
                    'timezone' => self::field('Zona horaria', 'text', 1, 40, ['required', 'string', 'min:2', 'max:80'], 'America/Mexico_City', false),
                    'available_schedule' => self::field('Horario disponible', 'textarea', 2, 10, ['required', 'string', 'min:10', 'max:1000'], null, false),
                    'staff_experience' => self::field('Experiencia como staff', 'textarea', 2, 20, ['required', 'string', 'min:30', 'max:2500']),
                    'staff_servers' => self::field('Servidores donde has sido staff', 'textarea', 2, 30, ['required', 'string', 'min:5', 'max:1500']),
                    'hacks_response' => self::field('Que harias si un usuario usa hacks?', 'textarea', 3, 10, ['required', 'string', 'min:40', 'max:2500']),
                    'insult_response' => self::field('Que harias si un usuario insulta al staff?', 'textarea', 3, 20, ['required', 'string', 'min:40', 'max:2500']),
                    'motivation' => self::field('Por que quieres ser staff?', 'textarea', 3, 30, ['required', 'string', 'min:40', 'max:2500']),
                    'contribution' => self::field('Que puedes aportar?', 'textarea', 3, 40, ['required', 'string', 'min:40', 'max:2500']),
                    'accept_rules' => self::field('Acepto las reglas de '.config('app.name', 'MineVida Network'), 'checkbox', 4, 10, ['accepted']),
                ]),
            ],
            'developer' => [
                'label' => 'Developer',
                'summary' => 'Plugins, integraciones, automatizacion y sistemas.',
                'description' => 'Ideal para personas con experiencia tecnica y criterio para trabajar en equipo.',
                'icon' => 'DE',
                'accent_color' => '#38bdf8',
                'sort_order' => 20,
                'steps' => self::defaultSteps(),
                'fields' => array_merge(self::commonFields(), [
                    'languages' => self::field('Lenguajes que dominas', 'textarea', 2, 10, ['required', 'string', 'min:10', 'max:1500']),
                    'java_experience' => self::field('Experiencia con Java', 'textarea', 2, 20, ['required', 'string', 'min:20', 'max:2500']),
                    'server_api_experience' => self::field('Experiencia con Spigot/Paper/Purpur', 'textarea', 2, 30, ['required', 'string', 'min:20', 'max:2500']),
                    'plugin_config_experience' => self::field('Experiencia configurando plugins', 'textarea', 2, 40, ['required', 'string', 'min:20', 'max:2500']),
                    'database_experience' => self::field('Experiencia con bases de datos', 'textarea', 2, 50, ['required', 'string', 'min:20', 'max:2500']),
                    'portfolio_url' => self::field('GitHub o portafolio', 'url', 2, 60, ['required', 'url:http,https', 'max:255']),
                    'previous_projects' => self::field('Proyectos anteriores', 'textarea', 2, 70, ['required', 'string', 'min:30', 'max:3000']),
                    'teamwork' => self::field('Puedes trabajar en equipo?', 'textarea', 3, 10, ['required', 'string', 'min:20', 'max:1500']),
                    'contribution' => self::field('Que podrias aportar a '.config('app.name', 'MineVida Network').'?', 'textarea', 3, 20, ['required', 'string', 'min:30', 'max:2500']),
                ]),
            ],
            'builder' => [
                'label' => 'Builder',
                'summary' => 'Lobbies, spawns, mapas, warzones y ambientes jugables.',
                'description' => 'Para constructores que cuidan estilo, composicion y detalle.',
                'icon' => 'BU',
                'accent_color' => '#22c55e',
                'sort_order' => 30,
                'steps' => self::defaultSteps(),
                'fields' => array_merge(self::commonFields(), [
                    'build_style' => self::field('Estilo de construccion favorito', 'text', 2, 10, ['required', 'string', 'min:3', 'max:120']),
                    'worldedit_experience' => self::field('Experiencia con WorldEdit', 'textarea', 2, 20, ['required', 'string', 'min:20', 'max:1800']),
                    'fawe_experience' => self::field('Experiencia con FAWE', 'textarea', 2, 30, ['required', 'string', 'min:20', 'max:1800']),
                    'voxelsniper_experience' => self::field('Experiencia con VoxelSniper si aplica', 'textarea', 2, 40, ['nullable', 'string', 'max:1800'], null, true, false),
                    'build_links' => self::field('Imagenes o links de builds anteriores', 'textarea_urls', 2, 50, ['required', 'string', 'min:10', 'max:2500']),
                    'available_schedule' => self::field('Disponibilidad', 'textarea', 2, 60, ['required', 'string', 'min:10', 'max:1000'], null, false),
                    'can_build' => self::field('Puedes construir lobby, spawn, mapas o warzones?', 'textarea', 3, 10, ['required', 'string', 'min:20', 'max:1800']),
                    'contribution' => self::field('Que puedes aportar?', 'textarea', 3, 20, ['required', 'string', 'min:30', 'max:2500']),
                ]),
            ],
            'multimedia' => [
                'label' => 'Multimedia',
                'summary' => 'Contenido visual, video, redes y presencia de marca.',
                'description' => 'Para creadores que pueden aportar piezas visuales y contenido para redes.',
                'icon' => 'MM',
                'accent_color' => '#a78bfa',
                'sort_order' => 40,
                'steps' => self::defaultSteps(),
                'fields' => array_merge(self::commonFields(), [
                    'primary_area' => self::field('Area principal', 'select', 2, 10, ['required'], null, true, true, [
                        'graphic_design' => 'Diseno grafico',
                        'video_editing' => 'Edicion de video',
                        'shorts' => 'TikTok/Reels/Shorts',
                        'renders' => 'Renders',
                        'community_manager' => 'Community manager',
                    ]),
                    'tools' => self::field('Programas que usas', 'textarea', 2, 20, ['required', 'string', 'min:10', 'max:1500']),
                    'portfolio_url' => self::field('Portafolio', 'url', 2, 30, ['required', 'url:http,https', 'max:255']),
                    'social_links' => self::field('Redes sociales', 'textarea_urls', 2, 40, ['required', 'string', 'min:10', 'max:2500']),
                    'work_examples' => self::field('Ejemplos de trabajos', 'textarea_urls', 2, 50, ['required', 'string', 'min:10', 'max:2500']),
                    'available_schedule' => self::field('Disponibilidad', 'textarea', 2, 60, ['required', 'string', 'min:10', 'max:1000'], null, false),
                    'content_plan' => self::field('Que tipo de contenido puedes crear para la comunidad?', 'textarea', 3, 10, ['required', 'string', 'min:30', 'max:2500']),
                ]),
            ],
        ];
    }

    private static function commonFields(): array
    {
        return [
            'minecraft_nick' => self::field('Nick de Minecraft', 'text', 1, 10, ['required', 'string', 'min:3', 'max:16', 'regex:/^[A-Za-z0-9_]+$/'], 'Tu nick exacto', false),
            'age' => self::field('Edad', 'number', 1, 20, ['required', 'integer', 'min:{min_age}', 'max:80'], '16', false),
            'country' => self::field('Pais', 'text', 1, 30, ['required', 'string', 'min:2', 'max:100'], 'Mexico', false),
        ];
    }

    private static function field(
        string $label,
        string $type,
        int $step,
        int $sortOrder,
        array $rules,
        ?string $placeholder = null,
        bool $isAnswer = true,
        bool $required = true,
        array $options = [],
    ): array {
        return [
            'label' => $label,
            'type' => $type,
            'placeholder' => $placeholder,
            'rules' => $rules,
            'options' => $options,
            'step' => $step,
            'sort_order' => $sortOrder,
            'is_answer' => $isAnswer,
            'required' => $required,
        ];
    }

    private static function definitionFromCategory(ApplicationCategory $category): array
    {
        $questions = $category->questions
            ->sortBy([['step', 'asc'], ['sort_order', 'asc'], ['id', 'asc']])
            ->values();

        $fields = $questions
            ->mapWithKeys(fn (ApplicationQuestion $question) => [$question->key => self::fieldFromQuestion($question)])
            ->all();

        $steps = collect($category->steps ?: self::defaultSteps())
            ->values()
            ->map(function (array $step, int $index) use ($questions) {
                $stepNumber = $index + 1;

                return [
                    'title' => $step['title'] ?? 'Paso '.$stepNumber,
                    'description' => $step['description'] ?? '',
                    'fields' => $questions
                        ->where('step', $stepNumber)
                        ->pluck('key')
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        $used = collect($steps)->flatMap(fn (array $step) => $step['fields'])->all();
        $missing = collect(array_keys($fields))->diff($used)->values()->all();

        if ($missing !== [] && $steps !== []) {
            $target = min(2, count($steps) - 1);
            $steps[$target]['fields'] = array_values(array_merge($steps[$target]['fields'], $missing));
        }

        return [
            'label' => $category->name,
            'summary' => $category->summary ?: 'Postulacion para '.$category->name.'.',
            'description' => $category->description,
            'icon' => $category->icon ?: str($category->name)->substr(0, 2)->upper()->toString(),
            'image_url' => $category->imageUrl(),
            'accent_color' => $category->accent_color,
            'minimum_age' => $category->minimum_age,
            'is_open' => $category->is_open,
            'fields' => $fields,
            'steps' => $steps,
        ];
    }

    private static function definitionFromDefault(string $type, array $definition): array
    {
        $fields = $definition['fields'];
        $steps = collect($definition['steps'])
            ->values()
            ->map(function (array $step, int $index) use ($fields) {
                $stepNumber = $index + 1;

                return [
                    'title' => $step['title'],
                    'description' => $step['description'],
                    'fields' => collect($fields)
                        ->filter(fn (array $field) => (int) ($field['step'] ?? 1) === $stepNumber)
                        ->keys()
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        return [
            'label' => $definition['label'],
            'summary' => $definition['summary'],
            'description' => $definition['description'] ?? null,
            'icon' => $definition['icon'] ?? str($definition['label'])->substr(0, 2)->upper()->toString(),
            'image_url' => null,
            'accent_color' => $definition['accent_color'] ?? null,
            'minimum_age' => $definition['minimum_age'] ?? null,
            'is_open' => true,
            'fields' => $fields,
            'steps' => $steps,
        ];
    }

    private static function fieldFromQuestion(ApplicationQuestion $question): array
    {
        return [
            'label' => $question->label,
            'type' => $question->input_type,
            'placeholder' => $question->placeholder,
            'help_text' => $question->help_text,
            'options' => $question->options ?: [],
            'rules' => self::rulesFromQuestion($question),
            'step' => $question->step,
            'sort_order' => $question->sort_order,
            'is_answer' => $question->is_answer,
            'required' => $question->is_required,
        ];
    }

    private static function rulesFromQuestion(ApplicationQuestion $question): array
    {
        $rules = collect($question->rules ?: [])
            ->map(fn ($rule) => trim((string) $rule))
            ->filter()
            ->values();

        $hasPresenceRule = $rules->contains(fn (string $rule) => in_array($rule, ['required', 'nullable', 'sometimes', 'accepted'], true));

        if (! $hasPresenceRule) {
            $rules->prepend($question->is_required
                ? ($question->input_type === 'checkbox' ? 'accepted' : 'required')
                : 'nullable');
        }

        if ($question->input_type === 'number' && ! $rules->contains('integer')) {
            $rules->push('integer');
        }

        if ($question->input_type === 'url' && ! $rules->contains(fn (string $rule) => str_starts_with($rule, 'url'))) {
            $rules->push('url:http,https');
        }

        if ($question->input_type === 'select' && ($question->options ?: []) !== [] && ! $rules->contains(fn (string $rule) => str_starts_with($rule, 'in:'))) {
            $rules->push('in:'.implode(',', array_keys($question->options)));
        }

        if (in_array($question->input_type, ['text', 'textarea', 'textarea_urls'], true) && ! $rules->contains('string')) {
            $rules->push('string');
        }

        return $rules->values()->all();
    }

    private static function hasDynamicCatalog(): bool
    {
        return Schema::hasTable('application_categories') && Schema::hasTable('application_questions');
    }
}
