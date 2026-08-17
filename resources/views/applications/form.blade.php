@php
    $steps = $definition['steps'];
    $fields = $definition['fields'];
    $errorKeys = collect($errors->getBag('default')->keys());
    $initialStep = 0;

    foreach ($steps as $stepIndex => $stepData) {
        if ($errorKeys->intersect($stepData['fields'])->isNotEmpty()) {
            $initialStep = $stepIndex;
            break;
        }
    }
@endphp

<x-layouts.user :title="'Formulario '.$definition['label'].' | '.config('app.name', 'MineVida Network')">
    <div class="mb-7 d-flex flex-column gap-4 flex-lg-row align-items-lg-center justify-content-lg-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">{{ $definition['label'] }}</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Formulario de postulacion</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Completa cada fase con calma. Asi el proceso se siente mas ligero y el equipo recibe respuestas mejor ordenadas.</p>
        </div>
        <x-lumoryx.user-dropdown />
    </div>

    <form
        class="lumoryx-panel p-5 sm:p-6"
        method="POST"
        action="{{ route('applications.store') }}"
        data-application-wizard
        data-initial-step="{{ $initialStep }}"
    >
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        @php
            $progressPercent = count($steps) > 1 ? round(($initialStep / (count($steps) - 1)) * 100) : 100;
        @endphp
        <div class="mb-5">
            <div class="mb-2 d-flex align-items-center justify-content-between text-xs font-semibold text-slate-400">
                <span data-progress-label>Paso {{ $initialStep + 1 }} de {{ count($steps) }}</span>
                <span data-progress-percent>{{ $progressPercent }}%</span>
            </div>
            <div class="lumoryx-progress-track">
                <div class="lumoryx-progress-fill" data-progress-fill style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>

        <div class="mb-6 d-grid gap-3 grid-cols-sm-2 grid-cols-xl-4">
            @foreach ($steps as $index => $stepData)
                @php
                    $isCurrent = $index === $initialStep;
                @endphp
                <button
                    class="rounded-lg border p-3 text-start transition {{ $isCurrent ? 'border-amber-300/30 bg-amber-300/10' : 'border-white/10 bg-white/[.025] opacity-70' }}"
                    type="button"
                    data-step-button="{{ $index }}"
                    @disabled($index > $initialStep)
                >
                    <div class="d-flex min-w-0 align-items-center gap-3">
                        <span
                            class="d-grid h-9 w-9 flex-shrink-0 place-items-center rounded-md border text-sm font-black {{ $isCurrent ? 'border-amber-300/30 bg-amber-300/15 text-amber-100' : 'border-white/10 bg-graphite-950/50 text-slate-400' }}"
                            data-step-number="{{ $index }}"
                        >{{ $index + 1 }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold {{ $isCurrent ? 'text-white' : 'text-slate-400' }}" data-step-title="{{ $index }}">{{ $stepData['title'] }}</p>
                            <p class="mt-1 d-none truncate text-xs text-slate-500 d-lg-block">{{ $stepData['description'] }}</p>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        <div style="--gtc: .3fr .7fr;" class="d-grid gap-6 grid-cols-custom-xl">
            <aside class="lumoryx-soft-panel p-5">
                @if (! empty($definition['image_url']))
                    <div class="lumoryx-category-media mb-5 rounded-lg border border-white/10">
                        <img src="{{ $definition['image_url'] }}" alt="">
                    </div>
                @endif
                <div class="d-flex align-items-start gap-4 d-xl-block">
                    @if (! empty($definition['image_url']))
                        <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100 xl:h-14 xl:w-14">{{ str($definition['label'])->substr(0, 2)->upper() }}</div>
                    @else
                        <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100 xl:h-14 xl:w-14">{{ str($definition['label'])->substr(0, 2)->upper() }}</div>
                    @endif
                    <div class="min-w-0 mt-xl-5">
                        <h2 class="text-xl font-black text-white">{{ $definition['label'] }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-400">{{ $definition['summary'] }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-white/10 bg-white/[.025] p-4">
                    <p class="text-sm font-semibold text-white">Como funciona</p>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Avanza por fases. Primero datos basicos, luego experiencia, despues situaciones y al final revisas antes de enviar.</p>
                </div>

                <div class="mt-4 rounded-lg border border-white/10 bg-white/[.025] p-4">
                    <p class="text-sm font-semibold text-white">Consejo</p>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Evita respuestas muy cortas. Explica que harias y por que, con ejemplos reales cuando puedas.</p>
                </div>
            </aside>

            <div class="min-w-0 rounded-lg border border-white/10 bg-graphite-950/25 p-5 sm:p-6">
                @foreach ($steps as $index => $stepData)
                    <section class="{{ $index !== $initialStep ? 'hidden' : '' }}" data-step-section="{{ $index }}">
                        <div class="mb-5 border-b border-white/10 pb-5">
                            <p class="lumoryx-kicker">Paso {{ $index + 1 }} de {{ count($steps) }}</p>
                            <h2 class="mt-2 text-2xl font-black text-white">{{ $stepData['title'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-400">{{ $stepData['description'] }}</p>
                        </div>

                        @if ($index === 0)
                            <div class="mb-5 d-grid gap-4 grid-cols-md-2">
                                <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Usuario Discord</p>
                                    <p class="lumoryx-break mt-2 font-semibold text-white">{{ auth()->user()->discord_global_name ?: auth()->user()->discord_username ?: auth()->user()->name }}</p>
                                    <p class="lumoryx-break mt-1 text-sm text-slate-400">{{ auth()->user()->discord_username }}</p>
                                </div>
                                <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Discord ID</p>
                                    <p class="lumoryx-break mt-2 font-semibold text-white">{{ auth()->user()->discord_id }}</p>
                                    <p class="mt-1 text-sm text-slate-400">Se toma automaticamente de tu inicio de sesion.</p>
                                </div>
                            </div>
                        @endif

                        @if ($index === count($steps) - 1)
                            <div class="mb-5 rounded-lg border border-amber-300/20 bg-amber-300/10 p-4">
                                <h3 class="font-bold text-white">Revision final</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Cuando envies la postulacion quedara pendiente de revision. El equipo podra cambiarla a revision, entrevista, aceptada o rechazada.</p>
                            </div>
                        @endif

                        @if ($stepData['fields'] !== [])
                            <div class="d-grid gap-5 grid-cols-md-2">
                                @foreach ($stepData['fields'] as $key)
                                    @php
                                        $field = $fields[$key];
                                        $rules = $field['rules'];
                                        $required = collect($rules)->contains(fn ($rule) => str_starts_with($rule, 'required') || $rule === 'accepted');
                                        $min = collect($rules)->first(fn ($rule) => str_starts_with($rule, 'min:'));
                                        $max = collect($rules)->first(fn ($rule) => str_starts_with($rule, 'max:'));
                                        $minValue = $min ? str($min)->after('min:')->toString() : null;
                                        $maxValue = $max ? str($max)->after('max:')->toString() : null;
                                        $minValue = $minValue === '{min_age}' ? (string) $minimumAge : $minValue;
                                        $fieldType = $field['type'] ?? 'text';
                                        $wide = in_array($fieldType, ['textarea', 'textarea_urls', 'checkbox'], true);
                                    @endphp

                                    <div class="{{ $wide ? 'md:col-span-2' : '' }}">
                                        @if ($fieldType === 'checkbox')
                                            <label class="d-flex flex-column gap-3 rounded-lg border border-white/10 bg-white/[.035] p-4 text-sm text-slate-300 flex-sm-row align-items-sm-center justify-content-sm-between">
                                                <span class="d-flex min-w-0 align-items-start gap-3">
                                                    <input class="mt-1 rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="{{ $key }}" value="1" @checked(old($key)) @required($required)>
                                                    <span class="lumoryx-break">{{ $field['label'] }}</span>
                                                </span>
                                                <a class="flex-shrink-0 font-semibold text-amber-200 hover:text-white" href="{{ route('rules') }}" target="_blank" rel="noopener noreferrer">Ver reglas</a>
                                            </label>
                                            @error($key)<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                                        @elseif (in_array($fieldType, ['textarea', 'textarea_urls'], true))
                                            <x-lumoryx.textarea
                                                :name="$key"
                                                :label="$field['label']"
                                                :required="$required"
                                                rows="5"
                                                :minlength="$minValue"
                                                :maxlength="$maxValue"
                                                placeholder="{{ $fieldType === 'textarea_urls' ? 'Pega uno o varios links, uno por linea.' : 'Escribe tu respuesta con detalle.' }}"
                                            >{{ old($key) }}</x-lumoryx.textarea>
                                        @elseif ($fieldType === 'select')
                                            <x-lumoryx.select :name="$key" :label="$field['label']" :required="$required">
                                                <option value="">Selecciona una opcion</option>
                                                @foreach ($field['options'] as $optionValue => $optionLabel)
                                                    <option value="{{ $optionValue }}" @selected(old($key) === $optionValue)>{{ $optionLabel }}</option>
                                                @endforeach
                                            </x-lumoryx.select>
                                        @else
                                            @php
                                                $inputType = $fieldType === 'url' ? 'url' : ($fieldType === 'number' ? 'number' : 'text');
                                                $inputMin = $fieldType === 'number' ? $minValue : null;
                                                $inputMax = $fieldType === 'number' ? $maxValue : null;
                                                $inputMinLength = $fieldType === 'number' ? null : $minValue;
                                                $inputMaxLength = $fieldType === 'number' ? null : $maxValue;
                                            @endphp
                                            <x-lumoryx.input
                                                :name="$key"
                                                :label="$field['label']"
                                                :required="$required"
                                                :value="old($key)"
                                                :type="$inputType"
                                                :placeholder="$field['placeholder'] ?? ''"
                                                :min="$inputMin"
                                                :max="$inputMax"
                                                :minlength="$inputMinLength"
                                                :maxlength="$inputMaxLength"
                                            />
                                        @endif

                                        @if (! empty($field['help_text']))
                                            <p class="mt-2 text-xs leading-5 text-slate-500">{{ $field['help_text'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg border border-white/10 bg-white/[.035] p-5">
                                <h3 class="font-bold text-white">Todo listo</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-400">No hay campos extra en esta fase. Puedes enviar cuando estes seguro de tus respuestas.</p>
                            </div>
                        @endif

                        <div class="mt-7 d-flex flex-column-reverse gap-3 border-t border-white/10 pt-5 flex-sm-row align-items-sm-center justify-content-sm-between">
                            <x-lumoryx.button variant="secondary" href="{{ route('applications.create') }}">Salir</x-lumoryx.button>

                            <div class="d-flex flex-column-reverse gap-3 flex-sm-row justify-content-sm-end">
                                @if ($index > 0)
                                    <button class="lumoryx-button-secondary" type="button" data-step-back>Atras</button>
                                @endif

                                @if ($index < count($steps) - 1)
                                    <button class="lumoryx-button-primary" type="button" data-step-next>Continuar</button>
                                @else
                                    <button class="lumoryx-button-primary" type="submit">Enviar postulacion</button>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </form>
</x-layouts.user>
