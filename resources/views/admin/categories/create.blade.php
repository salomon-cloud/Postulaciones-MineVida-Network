<x-layouts.admin :title="'Nueva categoria | '.config('app.name', 'MineVida Network')">
    <x-lumoryx.page-header kicker="Categorias" title="Nueva categoria" description="Al crearla se agregan preguntas basicas para que solo tengas que ajustar los detalles." glow="amber" glow2="emerald">
        <x-lumoryx.button variant="secondary" href="{{ route('admin.categories.index') }}">Volver</x-lumoryx.button>
    </x-lumoryx.page-header>

    <form class="lumoryx-panel mt-6 grid gap-5 p-5 sm:p-6 lg:grid-cols-2" method="POST" action="{{ route('admin.categories.store') }}" x-data="{ accentColor: '{{ old('accent_color', '#facc15') }}' }">
        @csrf

        <div class="lg:col-span-2">
            <p class="lumoryx-kicker">Informacion basica</p>
        </div>

        <x-lumoryx.input name="name" label="Nombre" value="{{ old('name') }}" placeholder="Soporte" required />
        <x-lumoryx.input name="slug" label="Slug" value="{{ old('slug') }}" placeholder="soporte" />

        <div class="lg:col-span-2">
            <x-lumoryx.textarea name="summary" label="Resumen" rows="3" required placeholder="Descripcion corta que vera el usuario.">{{ old('summary') }}</x-lumoryx.textarea>
        </div>

        <div class="lg:col-span-2">
            <x-lumoryx.textarea name="description" label="Descripcion interna" rows="3" placeholder="Notas o explicacion del area.">{{ old('description') }}</x-lumoryx.textarea>
        </div>

        <div class="lg:col-span-2 mt-2 border-t border-white/10 pt-5">
            <p class="lumoryx-kicker">Apariencia y disponibilidad</p>
        </div>

        <x-lumoryx.input name="icon" label="Icono corto" value="{{ old('icon') }}" maxlength="8" placeholder="SP" />
        <div>
            <label class="lumoryx-label" for="accent_color">Color</label>
            <div class="mt-2 flex items-center gap-3">
                <span class="h-11 w-11 shrink-0 rounded-md border border-white/15" :style="{ backgroundColor: accentColor }"></span>
                <input id="accent_color" class="lumoryx-input" type="text" name="accent_color" x-model="accentColor" placeholder="#facc15">
            </div>
        </div>
        <x-lumoryx.input name="minimum_age" label="Edad minima propia" type="number" min="10" max="80" value="{{ old('minimum_age') }}" placeholder="Opcional" />
        <x-lumoryx.input name="sort_order" label="Orden" type="number" min="0" value="{{ old('sort_order', 50) }}" required />

        <input type="hidden" name="is_open" value="0">
        <label class="lg:col-span-2 flex flex-col gap-4 rounded-lg border border-white/10 bg-white/[.035] p-4 text-sm text-slate-300 transition hover:border-amber-300/20 sm:flex-row sm:items-center sm:justify-between">
            <span>
                <span class="block font-semibold text-white">Categoria abierta</span>
                <span class="mt-1 block text-slate-400">Si esta activa, los usuarios podran enviar postulaciones a esta categoria.</span>
            </span>
            <input class="lumoryx-toggle-checkbox shrink-0" type="checkbox" name="is_open" value="1" @checked(old('is_open', true))>
        </label>

        <div class="lg:col-span-2 grid gap-4 lg:grid-cols-2">
            <x-lumoryx.input name="closed_until" label="Reapertura estimada si nace cerrada" type="datetime-local" value="{{ old('closed_until') }}" />
            <x-lumoryx.textarea name="closed_message" label="Mensaje si nace cerrada" rows="2" placeholder="Esta categoria abrira proximamente.">{{ old('closed_message') }}</x-lumoryx.textarea>
        </div>

        <div class="lg:col-span-2 mt-2 rounded-lg border border-white/10 bg-white/[.025] p-4">
            <h2 class="font-bold text-white">Fases del formulario</h2>
            <p class="mt-1 text-sm leading-6 text-slate-400">Define las secciones que vera el usuario. Puedes cambiarlas despues y agregar todas las preguntas que necesites.</p>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @php
                    $defaultSteps = old('steps', $steps);
                @endphp
                @for ($stepIndex = 0; $stepIndex < 6; $stepIndex++)
                    @php
                        $step = $defaultSteps[$stepIndex] ?? ['title' => '', 'description' => ''];
                    @endphp
                    <div class="rounded-lg border border-white/10 bg-graphite-950/30 p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Fase {{ $stepIndex + 1 }}</p>
                        <x-lumoryx.input
                            name="steps[{{ $stepIndex }}][title]"
                            label="Titulo"
                            value="{{ $step['title'] ?? '' }}"
                            placeholder="{{ $stepIndex === 0 ? 'Datos generales' : ($stepIndex === 1 ? 'Preguntas generales' : ($stepIndex === 2 ? 'Preguntas del staff' : 'Enviar')) }}"
                        />
                        <div class="mt-3">
                            <x-lumoryx.textarea
                                name="steps[{{ $stepIndex }}][description]"
                                label="Descripcion"
                                rows="2"
                                placeholder="Explica brevemente que se responde en esta fase."
                            >{{ $step['description'] ?? '' }}</x-lumoryx.textarea>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="lg:col-span-2 flex justify-end">
            <button class="lumoryx-button-primary" type="submit">Crear categoria</button>
        </div>
    </form>
</x-layouts.admin>
