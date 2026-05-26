@php
    $rulesText = fn ($rules) => collect($rules ?: [])->implode("\n");
    $optionsText = fn ($options) => collect($options ?: [])->map(fn ($label, $value) => $value.'='.$label)->implode("\n");
    $questionsByStep = $category->questions->groupBy('step');
    $visibleStepSlots = min(10, max(count($steps) + 2, 4));
    $activeTab = request('tab', 'info');
    $tabs = [
        'info' => ['number' => '1', 'label' => 'Informacion', 'description' => 'Nombre, resumen e identidad visual.'],
        'availability' => ['number' => '2', 'label' => 'Disponibilidad', 'description' => 'Abrir o cerrar temporalmente.'],
        'steps' => ['number' => '3', 'label' => 'Fases', 'description' => 'Pantallas del formulario.'],
        'questions' => ['number' => '4', 'label' => 'Preguntas', 'description' => 'Campos dentro de cada fase.'],
    ];

    if (! array_key_exists($activeTab, $tabs)) {
        $activeTab = 'info';
    }

    $activeSection = max(1, min((int) request('section', 1), max(count($steps), 1)));
    $activeStep = $steps[$activeSection - 1] ?? ['title' => 'Fase '.$activeSection, 'description' => ''];
    $activeQuestions = $questionsByStep->get($activeSection, collect());
    $editUrl = function (string $tab, ?int $section = null) use ($category) {
        $params = ['category' => $category, 'tab' => $tab];

        if ($section !== null) {
            $params['section'] = $section;
        }

        return route('admin.categories.edit', $params);
    };
@endphp

<x-layouts.admin :title="'Editar categoria | '.config('app.name', 'MineVida Network')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="lumoryx-kicker">Constructor de formulario</p>
                <h1 class="lumoryx-title">{{ $category->name }}</h1>
                <p class="mt-2 max-w-3xl text-slate-400">
                    Configura esta postulacion por partes. Cada seccion guarda una sola cosa para que no se mezcle todo.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <x-lumoryx.button variant="secondary" href="{{ route('applications.create.type', $category->slug) }}">
                    {{ $category->is_open ? 'Ver como usuario' : 'Ver aviso cerrado' }}
                </x-lumoryx.button>
                <x-lumoryx.button variant="secondary" href="{{ route('admin.categories.index') }}">Volver</x-lumoryx.button>
            </div>
        </div>

        @error('question')
            <div class="rounded-lg border border-rose-300/20 bg-rose-950/40 p-4 text-sm text-rose-100">{{ $message }}</div>
        @enderror

        <section class="lumoryx-panel p-3 sm:p-4">
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($tabs as $key => $tab)
                    <a
                        class="rounded-lg border p-4 transition {{ $activeTab === $key ? 'border-amber-300/40 bg-amber-300/10' : 'border-white/10 bg-white/[.025] hover:border-white/20 hover:bg-white/[.045]' }}"
                        href="{{ $editUrl($key, $key === 'questions' ? $activeSection : null) }}"
                    >
                        <span class="flex min-w-0 gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md border border-white/10 bg-graphite-950/55 text-sm font-black text-amber-100">{{ $tab['number'] }}</span>
                            <span class="min-w-0">
                                <span class="block truncate font-black text-white">{{ $tab['label'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $tab['description'] }}</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($activeTab === 'info')
            <section class="grid gap-6 xl:grid-cols-[1fr_.34fr]">
                <form class="lumoryx-panel p-5 sm:p-6" method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-white">Informacion principal</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Lo que el usuario ve antes de abrir el formulario.
                            </p>
                        </div>
                        <span class="w-fit rounded-full border border-white/10 bg-white/[.035] px-3 py-1 text-xs font-semibold text-slate-300">
                            {{ $category->applications_count }} postulaciones
                        </span>
                    </div>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <x-lumoryx.input name="name" label="Nombre visible" value="{{ old('name', $category->name) }}" required />
                        <x-lumoryx.input name="icon" label="Icono corto" value="{{ old('icon', $category->icon) }}" maxlength="8" placeholder="ST" />
                        <div class="lg:col-span-2">
                            <x-lumoryx.textarea name="summary" label="Resumen para el usuario" rows="4" required>{{ old('summary', $category->summary) }}</x-lumoryx.textarea>
                        </div>
                        <div class="lg:col-span-2 rounded-lg border border-white/10 bg-white/[.025] p-4">
                            <div class="grid gap-4 md:grid-cols-[220px_1fr] md:items-center">
                                <div class="lumoryx-category-media rounded-lg border border-white/10">
                                    @if ($category->imageUrl())
                                        <img src="{{ $category->imageUrl() }}" alt="">
                                    @else
                                        <div class="lumoryx-category-media-empty px-4 text-center text-sm text-slate-500">
                                            <span>Sin imagen asignada</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="lumoryx-label" for="category_image">Imagen de la categoria</label>
                                    <input class="lumoryx-input mt-2" id="category_image" name="category_image" type="file" accept="image/png,image/jpeg,image/webp">
                                    <p class="mt-2 text-xs leading-5 text-slate-500">Sube una nueva imagen para reemplazar la actual. JPG, PNG o WEBP, maximo 4 MB.</p>
                                    @if ($category->image_path)
                                        <label class="mt-3 flex items-start gap-3 rounded-lg border border-rose-300/20 bg-rose-300/10 p-3 text-sm text-rose-100">
                                            <input class="mt-1 rounded border-white/10 bg-graphite-950 text-rose-300 focus:ring-rose-300" type="checkbox" name="remove_image" value="1">
                                            <span>Quitar imagen actual</span>
                                        </label>
                                    @endif
                                    @error('category_image')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <details class="mt-5 rounded-lg border border-white/10 bg-white/[.025] p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-white">Ajustes avanzados</summary>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <x-lumoryx.input name="slug" label="URL interna" value="{{ old('slug', $category->slug) }}" :disabled="$category->applications_count > 0" required />
                            <x-lumoryx.input name="accent_color" label="Color" value="{{ old('accent_color', $category->accent_color) }}" placeholder="#facc15" />
                            <x-lumoryx.input name="minimum_age" label="Edad minima propia" type="number" min="10" max="80" value="{{ old('minimum_age', $category->minimum_age) }}" />
                            <x-lumoryx.input name="sort_order" label="Orden en listado" type="number" min="0" value="{{ old('sort_order', $category->sort_order) }}" required />
                            <div class="sm:col-span-2">
                                <x-lumoryx.textarea name="description" label="Notas internas" rows="3">{{ old('description', $category->description) }}</x-lumoryx.textarea>
                            </div>
                        </div>
                    </details>

                    @foreach ($steps as $stepIndex => $step)
                        <input type="hidden" name="steps[{{ $stepIndex }}][title]" value="{{ $step['title'] }}">
                        <input type="hidden" name="steps[{{ $stepIndex }}][description]" value="{{ $step['description'] }}">
                    @endforeach
                    <input type="hidden" name="is_open" value="{{ $category->is_open ? 1 : 0 }}">

                    <div class="mt-6 flex justify-end">
                        <button class="lumoryx-button-primary" type="submit">Guardar informacion</button>
                    </div>
                </form>

                <aside class="lumoryx-panel h-fit p-5">
                    <p class="lumoryx-kicker">Vista rapida</p>
                    <div class="mt-4 flex items-start gap-4 rounded-lg border border-white/10 bg-white/[.025] p-4">
                        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-md border border-white/10 bg-graphite-950/60 text-lg font-black text-amber-100">
                            {{ $category->icon ?: str($category->name)->substr(0, 2)->upper() }}
                        </span>
                        <div class="min-w-0">
                            <p class="lumoryx-break text-xl font-black text-white">{{ $category->name }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-400">{{ $category->summary }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex items-center justify-between rounded-lg border border-white/10 bg-white/[.025] px-4 py-3">
                            <dt class="text-slate-400">Estado</dt>
                            <dd class="{{ $category->is_open ? 'text-emerald-200' : 'text-rose-200' }}">{{ $category->is_open ? 'Abierta' : 'Cerrada' }}</dd>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-white/10 bg-white/[.025] px-4 py-3">
                            <dt class="text-slate-400">Fases</dt>
                            <dd class="text-white">{{ count($steps) }}</dd>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-white/10 bg-white/[.025] px-4 py-3">
                            <dt class="text-slate-400">Preguntas</dt>
                            <dd class="text-white">{{ $category->questions->count() }}</dd>
                        </div>
                    </dl>
                </aside>
            </section>
        @endif

        @if ($activeTab === 'availability')
            <section class="grid gap-6 xl:grid-cols-[.58fr_.42fr]">
                <form class="lumoryx-panel p-5 sm:p-6" method="POST" action="{{ route('admin.categories.availability', $category) }}">
                    @csrf
                    @method('PATCH')

                    <div class="max-w-3xl">
                        <h2 class="text-2xl font-black text-white">Disponibilidad</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Cierra temporalmente sin borrar la categoria. El usuario vera un aviso claro en lugar del formulario.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4">
                        <label class="flex items-start gap-4 rounded-lg border border-emerald-300/20 bg-emerald-300/10 p-5 text-sm text-emerald-100">
                            <input class="mt-1 border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="radio" name="is_open" value="1" @checked($category->is_open)>
                            <span>
                                <span class="block text-lg font-black text-white">Abierta</span>
                                <span class="mt-2 block leading-6 text-emerald-100/80">Los usuarios pueden enviar postulaciones nuevas.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-4 rounded-lg border border-rose-300/20 bg-rose-300/10 p-5 text-sm text-rose-100">
                            <input class="mt-1 border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="radio" name="is_open" value="0" @checked(! $category->is_open)>
                            <span>
                                <span class="block text-lg font-black text-white">Cerrada temporalmente</span>
                                <span class="mt-2 block leading-6 text-rose-100/80">El usuario no podra postular hasta que la reabras.</span>
                            </span>
                        </label>
                    </div>

                    <div class="mt-6 grid gap-5">
                        <x-lumoryx.input
                            name="closed_until"
                            label="Fecha estimada de reapertura"
                            type="datetime-local"
                            value="{{ old('closed_until', $category->closed_until?->format('Y-m-d\TH:i')) }}"
                        />
                        <x-lumoryx.textarea
                            name="closed_message"
                            label="Mensaje para el usuario"
                            rows="4"
                            placeholder="Esta categoria esta cerrada temporalmente. Vuelve a revisar pronto."
                        >{{ old('closed_message', $category->closed_message) }}</x-lumoryx.textarea>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button class="lumoryx-button-primary" type="submit">Guardar disponibilidad</button>
                    </div>
                </form>

                <aside class="lumoryx-panel h-fit p-5">
                    <p class="lumoryx-kicker">Como lo vera el usuario</p>
                    <div class="mt-4 rounded-lg border {{ $category->is_open ? 'border-emerald-300/20 bg-emerald-300/10' : 'border-rose-300/20 bg-rose-300/10' }} p-5">
                        <p class="text-xl font-black text-white">{{ $category->is_open ? 'Postulaciones abiertas' : 'Categoria cerrada' }}</p>
                        <p class="mt-3 text-sm leading-6 text-slate-300">
                            {{ $category->is_open ? 'El formulario aparece disponible para enviar.' : ($category->closed_message ?: 'Esta categoria esta cerrada temporalmente.') }}
                        </p>
                        @if (! $category->is_open && $category->closed_until)
                            <p class="mt-4 text-sm font-semibold text-amber-100">Reapertura estimada: {{ $category->closed_until->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </aside>
            </section>
        @endif

        @if ($activeTab === 'steps')
            <form class="lumoryx-panel p-5 sm:p-6" method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="name" value="{{ old('name', $category->name) }}">
                <input type="hidden" name="icon" value="{{ old('icon', $category->icon) }}">
                <input type="hidden" name="summary" value="{{ old('summary', $category->summary) }}">
                <input type="hidden" name="slug" value="{{ old('slug', $category->slug) }}">
                <input type="hidden" name="accent_color" value="{{ old('accent_color', $category->accent_color) }}">
                <input type="hidden" name="minimum_age" value="{{ old('minimum_age', $category->minimum_age) }}">
                <input type="hidden" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}">
                <input type="hidden" name="description" value="{{ old('description', $category->description) }}">
                <input type="hidden" name="is_open" value="{{ $category->is_open ? 1 : 0 }}">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <h2 class="text-2xl font-black text-white">Fases del formulario</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Cada fase es una pantalla. Usa nombres simples: Datos generales, Preguntas del staff, Preguntas de SS y Enviar.
                        </p>
                    </div>
                    <a class="lumoryx-button-secondary" href="{{ $editUrl('questions', $activeSection) }}">Ir a preguntas</a>
                </div>

                <div class="mt-6 grid gap-4">
                    @for ($stepIndex = 0; $stepIndex < $visibleStepSlots; $stepIndex++)
                        @php
                            $step = $steps[$stepIndex] ?? ['title' => '', 'description' => ''];
                            $questionCount = $questionsByStep->get($stepIndex + 1, collect())->count();
                        @endphp
                        <div class="rounded-lg border border-white/10 bg-white/[.025] p-4">
                            <div class="grid gap-4 lg:grid-cols-[10rem_1fr_1fr] lg:items-end">
                                <div>
                                    <p class="font-black text-white">Fase {{ $stepIndex + 1 }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $questionCount }} preguntas</p>
                                </div>
                                <x-lumoryx.input
                                    name="steps[{{ $stepIndex }}][title]"
                                    label="Titulo"
                                    value="{{ old('steps.'.$stepIndex.'.title', $step['title'] ?? '') }}"
                                    placeholder="{{ $stepIndex === 0 ? 'Datos generales' : ($stepIndex === 1 ? 'Preguntas generales' : ($stepIndex === 2 ? 'Preguntas del staff' : 'Enviar')) }}"
                                />
                                <x-lumoryx.input
                                    name="steps[{{ $stepIndex }}][description]"
                                    label="Descripcion corta"
                                    value="{{ old('steps.'.$stepIndex.'.description', $step['description'] ?? '') }}"
                                    placeholder="Que responde el usuario aqui?"
                                />
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="mt-6 flex justify-end">
                    <button class="lumoryx-button-primary" type="submit">Guardar fases</button>
                </div>
            </form>
        @endif

        @if ($activeTab === 'questions')
            <section class="space-y-6">
                <div class="lumoryx-panel p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-white">Preguntas por fase</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Elige una fase y trabaja solo sus preguntas. Las demas quedan fuera de la vista.
                            </p>
                        </div>
                        <a class="lumoryx-button-secondary" href="{{ $editUrl('steps') }}">Editar fases</a>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($steps as $index => $step)
                            @php
                                $stepNumber = $index + 1;
                            @endphp
                            <a
                                class="rounded-lg border p-4 transition {{ $activeSection === $stepNumber ? 'border-amber-300/40 bg-amber-300/10' : 'border-white/10 bg-white/[.025] hover:border-white/20 hover:bg-white/[.045]' }}"
                                href="{{ $editUrl('questions', $stepNumber) }}"
                            >
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md border border-white/10 bg-graphite-950/50 text-sm font-black text-amber-100">{{ $stepNumber }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-black text-white">{{ $step['title'] }}</span>
                                        <span class="mt-1 block text-sm text-slate-500">{{ $questionsByStep->get($stepNumber, collect())->count() }} preguntas</span>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/[.025] p-5">
                    <p class="lumoryx-kicker">Fase {{ $activeSection }} de {{ count($steps) }}</p>
                    <h3 class="mt-2 text-2xl font-black text-white">{{ $activeStep['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ $activeStep['description'] }}</p>
                </div>

                <details class="lumoryx-panel p-5 sm:p-6">
                    <summary class="cursor-pointer list-none">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-black text-white">Agregar pregunta a {{ $activeStep['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Abre esta seccion solo cuando quieras crear una pregunta nueva.</p>
                            </div>
                            <span class="lumoryx-button-primary inline-flex">Agregar pregunta</span>
                        </div>
                    </summary>
                    <form class="mt-5 border-t border-white/10 pt-5" method="POST" action="{{ route('admin.categories.questions.store', $category) }}">
                        @csrf
                        @include('admin.categories.partials.question-fields', [
                            'question' => null,
                            'inputTypes' => $inputTypes,
                            'steps' => $steps,
                            'rulesText' => $rulesText,
                            'optionsText' => $optionsText,
                            'submitLabel' => 'Guardar pregunta',
                            'fixedStep' => $activeSection,
                        ])
                    </form>
                </details>

                <section class="lumoryx-panel p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-xl font-black text-white">Preguntas de {{ $activeStep['title'] }}</h3>
                        <span class="w-fit rounded-full border border-white/10 bg-white/[.035] px-3 py-1 text-xs font-semibold text-slate-300">{{ $activeQuestions->count() }} total</span>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($activeQuestions as $question)
                            <details class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                                <summary class="cursor-pointer list-none">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="lumoryx-break text-lg font-black text-white">{{ $question->label }}</p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $inputTypes[$question->input_type] ?? $question->input_type }}
                                                - Orden {{ $question->sort_order }}
                                                - {{ $question->is_required ? 'Obligatoria' : 'Opcional' }}
                                            </p>
                                        </div>
                                        <span class="text-xs font-semibold text-amber-200">Editar</span>
                                    </div>
                                </summary>

                                <div class="mt-5 border-t border-white/10 pt-5">
                                    <form method="POST" action="{{ route('admin.categories.questions.update', [$category, $question]) }}">
                                        @csrf
                                        @method('PATCH')
                                        @include('admin.categories.partials.question-fields', [
                                            'question' => $question,
                                            'inputTypes' => $inputTypes,
                                            'steps' => $steps,
                                            'rulesText' => $rulesText,
                                            'optionsText' => $optionsText,
                                            'submitLabel' => 'Guardar cambios',
                                        ])
                                    </form>
                                    <form
                                        class="mt-3 flex justify-end"
                                        method="POST"
                                        action="{{ route('admin.categories.questions.destroy', [$category, $question]) }}"
                                        data-confirm
                                        data-confirm-title="Eliminar pregunta"
                                        data-confirm-message="La pregunta {{ $question->label }} se quitara del formulario. Esta accion no afecta respuestas ya enviadas."
                                        data-confirm-confirm-text="Eliminar pregunta"
                                        data-confirm-tone="danger"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="lumoryx-button-danger px-3 py-2" type="submit">Eliminar pregunta</button>
                                    </form>
                                </div>
                            </details>
                        @empty
                            <div class="rounded-lg border border-dashed border-white/10 bg-white/[.025] p-5 text-sm text-slate-500">
                                Esta fase todavia no tiene preguntas. Usa el boton de arriba para agregar la primera.
                            </div>
                        @endforelse
                    </div>
                </section>
            </section>
        @endif
    </div>
</x-layouts.admin>
