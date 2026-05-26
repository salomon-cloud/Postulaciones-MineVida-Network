<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationCategory;
use App\Models\ApplicationQuestion;
use App\Services\DiscordSystemLogService;
use App\Support\ApplicationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ApplicationCategory::query()
            ->withTrashed()
            ->withCount(['applications', 'questions'])
            ->ordered()
            ->paginate(12);

        $stats = [
            'total' => ApplicationCategory::withTrashed()->count(),
            'open' => ApplicationCategory::query()->where('is_open', true)->count(),
            'closed' => ApplicationCategory::query()->where('is_open', false)->count(),
            'archived' => ApplicationCategory::onlyTrashed()->count(),
            'questions' => ApplicationQuestion::query()->count(),
            'applications' => Application::query()->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'steps' => ApplicationCatalog::defaultSteps(),
        ]);
    }

    public function store(Request $request, DiscordSystemLogService $systemLogs): RedirectResponse
    {
        $data = $this->categoryData($request);

        $category = DB::transaction(function () use ($data) {
            $category = ApplicationCategory::query()->create($data);

            $this->createStarterQuestions($category);

            return $category;
        });

        $systemLogs->queue(
            'categories',
            'Categoria creada',
            'Se creo una nueva categoria de postulacion.',
            [
                'Categoria' => $category->name,
                'Slug' => $category->slug,
                'Estado' => $category->is_open ? 'Abierta' : 'Cerrada',
            ],
            'success',
            $request->user(),
            $request,
        );

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('success', 'Categoria creada. Ahora puedes ajustar sus preguntas.');
    }

    public function edit(ApplicationCategory $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category->load(['questions' => fn ($query) => $query->orderBy('step')->orderBy('sort_order')->orderBy('id')])
                ->loadCount('applications'),
            'steps' => $category->steps ?: ApplicationCatalog::defaultSteps(),
            'inputTypes' => ApplicationQuestion::inputTypes(),
        ]);
    }

    public function update(
        Request $request,
        ApplicationCategory $category,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $data = $this->categoryData($request, $category);

        if ($category->applications()->exists()) {
            $data['slug'] = $category->slug;
        }

        $category->update($data);

        $systemLogs->queue(
            'categories',
            'Categoria actualizada',
            'Se editaron datos o fases de una categoria.',
            [
                'Categoria' => $category->name,
                'Slug' => $category->slug,
                'Fases' => count($category->steps ?: []),
            ],
            'warning',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Categoria actualizada.');
    }

    public function destroy(Request $request, ApplicationCategory $category, DiscordSystemLogService $systemLogs): RedirectResponse
    {
        if ($category->applications()->exists()) {
            $category->update([
                'is_open' => false,
                'closed_message' => $category->closed_message ?: 'Esta categoria fue cerrada temporalmente por el equipo.',
            ]);

            $systemLogs->queue(
                'categories',
                'Categoria cerrada por seguridad',
                'La categoria tenia postulaciones, asi que se cerro en lugar de archivarse.',
                [
                    'Categoria' => $category->name,
                    'Postulaciones' => $category->applications()->count(),
                ],
                'danger',
                $request->user(),
                $request,
            );

            return back()->with('info', 'La categoria tiene postulaciones, asi que se cerro en lugar de eliminarse.');
        }

        $category->delete();

        $systemLogs->queue(
            'categories',
            'Categoria archivada',
            'Se archivo una categoria sin postulaciones.',
            [
                'Categoria' => $category->name,
                'Slug' => $category->slug,
            ],
            'danger',
            $request->user(),
            $request,
        );

        return redirect()->route('admin.categories.index')->with('success', 'Categoria archivada. Puedes rehabilitarla cuando quieras.');
    }

    public function restore(Request $request, int $category, DiscordSystemLogService $systemLogs): RedirectResponse
    {
        $category = ApplicationCategory::withTrashed()->findOrFail($category);

        $category->restore();
        $category->update(['is_open' => true]);

        $systemLogs->queue(
            'categories',
            'Categoria rehabilitada',
            'Se rehabilito una categoria archivada y quedo abierta.',
            [
                'Categoria' => $category->name,
                'Slug' => $category->slug,
            ],
            'success',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Categoria rehabilitada y abierta.');
    }

    public function updateAvailability(
        Request $request,
        ApplicationCategory $category,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $validated = $request->validate([
            'is_open' => ['required', 'boolean'],
            'closed_until' => ['nullable', 'date', 'after:now'],
            'closed_message' => ['nullable', 'string', 'max:500'],
        ]);

        $isOpen = (bool) $validated['is_open'];

        $category->update([
            'is_open' => $isOpen,
            'closed_until' => $isOpen ? null : ($validated['closed_until'] ?? null),
            'closed_message' => $isOpen ? null : ($validated['closed_message'] ?? 'Esta categoria esta cerrada temporalmente.'),
        ]);

        $systemLogs->queue(
            'categories',
            $isOpen ? 'Categoria reabierta' : 'Categoria cerrada temporalmente',
            $isOpen
                ? 'Los usuarios ya pueden enviar postulaciones en esta categoria.'
                : 'La categoria se cerro temporalmente para los usuarios.',
            [
                'Categoria' => $category->name,
                'Reapertura' => $category->closed_until?->format('d/m/Y H:i') ?: 'Sin fecha',
                'Mensaje' => $category->closed_message ?: '-',
            ],
            $isOpen ? 'success' : 'danger',
            $request->user(),
            $request,
        );

        return back()->with('success', $isOpen ? 'Categoria reabierta.' : 'Categoria cerrada temporalmente.');
    }

    public function storeQuestion(
        Request $request,
        ApplicationCategory $category,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $question = $category->questions()->create($this->questionData($request, $category));

        $systemLogs->queue(
            'categories',
            'Pregunta agregada',
            'Se agrego una pregunta a una categoria.',
            [
                'Categoria' => $category->name,
                'Pregunta' => $question->label,
                'Fase' => 'Fase '.$question->step,
            ],
            'success',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Pregunta agregada.');
    }

    public function updateQuestion(
        Request $request,
        ApplicationCategory $category,
        ApplicationQuestion $question,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        abort_unless($question->category_id === $category->id, 404);

        $question->update($this->questionData($request, $category, $question));

        $systemLogs->queue(
            'categories',
            'Pregunta actualizada',
            'Se editaron datos de una pregunta.',
            [
                'Categoria' => $category->name,
                'Pregunta' => $question->label,
                'Fase' => 'Fase '.$question->step,
            ],
            'warning',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Pregunta actualizada.');
    }

    public function destroyQuestion(
        Request $request,
        ApplicationCategory $category,
        ApplicationQuestion $question,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        abort_unless($question->category_id === $category->id, 404);

        if (in_array($question->key, ['minecraft_nick', 'age', 'country'], true)) {
            return back()->withErrors(['question' => 'No puedes eliminar los campos basicos obligatorios.']);
        }

        $question->delete();

        $systemLogs->queue(
            'categories',
            'Pregunta eliminada',
            'Se elimino una pregunta de una categoria.',
            [
                'Categoria' => $category->name,
                'Pregunta' => $question->label,
                'Clave' => $question->key,
            ],
            'danger',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Pregunta eliminada.');
    }

    private function categoryData(Request $request, ?ApplicationCategory $category = null): array
    {
        $slug = $category?->applications()->exists()
            ? $category->slug
            : str($request->input('slug') ?: $request->input('name'))->slug()->toString();
        $request->merge(['slug' => $slug]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'slug' => [
                'required',
                'string',
                'min:2',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('application_categories', 'slug')->ignore($category),
            ],
            'summary' => ['required', 'string', 'min:5', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:8'],
            'accent_color' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'category_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'minimum_age' => ['nullable', 'integer', 'min:10', 'max:80'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_open' => ['nullable', 'boolean'],
            'closed_until' => ['nullable', 'date', 'after:now'],
            'closed_message' => ['nullable', 'string', 'max:500'],
            'steps' => ['nullable', 'array', 'max:10'],
            'steps.*.title' => ['nullable', 'string', 'max:80'],
            'steps.*.description' => ['nullable', 'string', 'max:220'],
        ]);

        $validated['is_open'] = $request->has('is_open') ? $request->boolean('is_open') : ($category?->is_open ?? true);
        $validated['closed_until'] = $validated['is_open'] ? null : ($validated['closed_until'] ?? $category?->closed_until);
        $validated['closed_message'] = $validated['is_open'] ? null : ($validated['closed_message'] ?? $category?->closed_message);
        $validated['accent_color'] = $validated['accent_color']
            ? '#'.ltrim($validated['accent_color'], '#')
            : null;
        $validated['steps'] = $this->normalizeSteps($request->input('steps', $category?->steps ?: ApplicationCatalog::defaultSteps()));
        $validated['image_path'] = $this->imagePath($request, $category);

        unset($validated['category_image'], $validated['remove_image']);

        if ($category) {
            $this->moveQuestionsIntoValidSteps($category, count($validated['steps']));
        }

        return $validated;
    }

    private function imagePath(Request $request, ?ApplicationCategory $category = null): ?string
    {
        $currentPath = $category?->image_path;

        if ($request->boolean('remove_image')) {
            $this->deleteCategoryImage($currentPath);

            return null;
        }

        if (! $request->hasFile('category_image')) {
            return $currentPath;
        }

        $path = $request->file('category_image')->store('categories', 'public');

        if ($path) {
            $this->deleteCategoryImage($currentPath);
        }

        return $path ?: $currentPath;
    }

    private function deleteCategoryImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'categories/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function questionData(Request $request, ApplicationCategory $category, ?ApplicationQuestion $question = null): array
    {
        $validated = $request->validate([
            'key' => [
                'nullable',
                'string',
                'min:2',
                'max:64',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('application_questions', 'key')
                    ->where('category_id', $category->id)
                    ->ignore($question),
            ],
            'label' => ['required', 'string', 'min:2', 'max:160'],
            'input_type' => ['required', Rule::in(array_keys(ApplicationQuestion::inputTypes()))],
            'placeholder' => ['nullable', 'string', 'max:160'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'options_text' => ['nullable', 'string', 'max:2000'],
            'rules_text' => ['nullable', 'string', 'max:2000'],
            'step' => ['required', 'integer', 'min:1', 'max:'.count($category->steps ?: ApplicationCatalog::defaultSteps())],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_required' => ['nullable', 'boolean'],
            'is_answer' => ['nullable', 'boolean'],
        ]);

        $key = $this->questionKey($category, (string) ($validated['key'] ?? ''), $validated['label'], $question);
        $isColumnField = in_array($key, ApplicationCatalog::columnFields(), true);

        return [
            'key' => $key,
            'label' => $validated['label'],
            'input_type' => $validated['input_type'],
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'options' => $this->parseOptions($validated['options_text'] ?? '', $validated['input_type']),
            'rules' => $this->parseRules($validated['rules_text'] ?? '', $validated['input_type'], $request->boolean('is_required')),
            'step' => (int) $validated['step'],
            'is_required' => $request->boolean('is_required'),
            'is_answer' => $isColumnField ? false : $request->boolean('is_answer', true),
            'sort_order' => (int) $validated['sort_order'],
        ];
    }

    private function createStarterQuestions(ApplicationCategory $category): void
    {
        $defaults = ApplicationCatalog::defaultDefinitions()['staff']['fields'];
        $starterKeys = [
            'minecraft_nick',
            'age',
            'country',
            'timezone',
            'available_schedule',
            'motivation',
            'contribution',
            'accept_rules',
        ];

        foreach ($starterKeys as $key) {
            $field = $defaults[$key];

            $category->questions()->create([
                'key' => $key,
                'label' => $field['label'],
                'input_type' => $field['type'],
                'placeholder' => $field['placeholder'] ?? null,
                'options' => $field['options'] ?? [],
                'rules' => $field['rules'],
                'step' => $field['step'],
                'is_required' => $field['required'] ?? true,
                'is_answer' => $field['is_answer'] ?? ! in_array($key, ApplicationCatalog::columnFields(), true),
                'sort_order' => $field['sort_order'],
            ]);
        }
    }

    private function normalizeSteps(array $steps): array
    {
        $normalized = collect($steps)
            ->map(function ($step) {
                $title = trim((string) ($step['title'] ?? ''));
                $description = trim((string) ($step['description'] ?? ''));

                if ($title === '') {
                    return null;
                }

                return [
                    'title' => strip_tags($title),
                    'description' => strip_tags($description),
                ];
            })
            ->filter()
            ->values()
            ->take(10)
            ->all();

        if ($normalized === []) {
            return ApplicationCatalog::defaultSteps();
        }

        return $normalized;
    }

    private function moveQuestionsIntoValidSteps(ApplicationCategory $category, int $stepCount): void
    {
        $category->questions()
            ->where('step', '>', $stepCount)
            ->update(['step' => $stepCount]);
    }

    private function parseRules(string $rulesText, string $inputType, bool $isRequired): array
    {
        $rules = collect(preg_split('/[\r\n,]+/', $rulesText))
            ->map(fn (string $rule) => trim($rule))
            ->filter()
            ->values();

        if ($rules->isNotEmpty()) {
            return $rules->all();
        }

        $rules->push($isRequired ? ($inputType === 'checkbox' ? 'accepted' : 'required') : 'nullable');

        if ($inputType === 'number') {
            $rules->push('integer');
        } elseif ($inputType === 'url') {
            $rules->push('url:http,https', 'max:255');
        } elseif ($inputType !== 'checkbox') {
            $rules->push('string', 'max:2500');
        }

        return $rules->all();
    }

    private function parseOptions(string $optionsText, string $inputType): ?array
    {
        if ($inputType !== 'select') {
            return null;
        }

        return collect(preg_split('/\r\n|\r|\n/', $optionsText))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->mapWithKeys(function (string $line) {
                if (str_contains($line, '=')) {
                    [$value, $label] = array_map('trim', explode('=', $line, 2));
                } else {
                    $label = $line;
                    $value = str($line)->slug('_')->toString();
                }

                return [$value => $label];
            })
            ->all();
    }

    private function questionKey(
        ApplicationCategory $category,
        string $key,
        string $label,
        ?ApplicationQuestion $question = null,
    ): string {
        $key = trim($key);

        if ($key === '' && $question) {
            return $question->key;
        }

        $base = $key !== ''
            ? $key
            : str($label)->slug('_')->toString();

        if (! preg_match('/^[a-z]/', $base)) {
            $base = 'question_'.$base;
        }

        $base = preg_replace('/[^a-z0-9_]/', '_', $base) ?: 'question';
        $candidate = str($base)->limit(58, '')->toString();
        $suffix = 2;

        while ($category->questions()
            ->where('key', $candidate)
            ->when($question, fn ($query) => $query->whereKeyNot($question->id))
            ->exists()) {
            $candidate = str($base)->limit(55, '')->toString().'_'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
