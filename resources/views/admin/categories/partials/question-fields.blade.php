@php
    $fixedStep = $fixedStep ?? null;
    $questionKey = old('key', $question?->key ?? '');
    $questionType = old('input_type', $question?->input_type ?? 'textarea');
    $selectedStep = (int) old('step', $question?->step ?? ($fixedStep ?: min(2, max(1, count($steps)))));
    $isRequired = old('is_required', $question?->is_required ?? true);
@endphp

<div class="mt-5 space-y-5">
    <input type="hidden" name="is_answer" value="1">
    @if ($fixedStep)
        <input type="hidden" name="step" value="{{ $fixedStep }}">
    @endif

    <div class="grid gap-4 lg:grid-cols-[1fr_.38fr]">
        <div>
            <x-lumoryx.input
                name="label"
                label="Pregunta que vera el usuario"
                value="{{ old('label', $question?->label) }}"
                placeholder="Que harias si ves a un usuario usando hacks?"
                required
            />
            <p class="mt-2 text-xs leading-5 text-slate-500">Escribela como quieres que aparezca en el formulario.</p>
        </div>

        <x-lumoryx.select name="input_type" label="Tipo de respuesta" required>
            @foreach ($inputTypes as $value => $label)
                <option value="{{ $value }}" @selected($questionType === $value)>{{ $label }}</option>
            @endforeach
        </x-lumoryx.select>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @if ($fixedStep)
            <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Seccion</p>
                <p class="mt-2 font-semibold text-white">{{ $fixedStep }}. {{ $steps[$fixedStep - 1]['title'] ?? 'Seccion '.$fixedStep }}</p>
            </div>
        @else
            <x-lumoryx.select name="step" label="Mover a seccion" required>
                @foreach ($steps as $index => $step)
                    <option value="{{ $index + 1 }}" @selected($selectedStep === $index + 1)>{{ $index + 1 }}. {{ $step['title'] }}</option>
                @endforeach
            </x-lumoryx.select>
        @endif

        <x-lumoryx.input
            name="sort_order"
            label="Orden dentro de la seccion"
            type="number"
            min="0"
            value="{{ old('sort_order', $question?->sort_order ?? 50) }}"
            required
        />

        <label class="flex h-full items-start gap-3 rounded-lg border border-white/10 bg-white/[.035] p-4 text-sm text-slate-300">
            <input class="mt-1 rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="is_required" value="1" @checked($isRequired)>
            <span>
                <span class="block font-semibold text-white">Obligatoria</span>
                <span class="mt-1 block text-slate-400">El usuario debe responderla.</span>
            </span>
        </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <x-lumoryx.input
            name="placeholder"
            label="Ejemplo dentro del campo"
            value="{{ old('placeholder', $question?->placeholder) }}"
            placeholder="Describe la situacion con detalle..."
        />

        <x-lumoryx.textarea
            name="help_text"
            label="Ayuda debajo de la pregunta"
            rows="2"
            placeholder="Puedes dejar una pista para que el usuario responda mejor."
        >{{ old('help_text', $question?->help_text) }}</x-lumoryx.textarea>
    </div>

    <details class="rounded-lg border border-white/10 bg-white/[.025] p-4">
        <summary class="cursor-pointer text-sm font-semibold text-white">Opciones y ajustes avanzados</summary>
        <div class="mt-4 grid gap-4">
            <x-lumoryx.textarea
                name="options_text"
                label="Opciones si el tipo es seleccion"
                rows="4"
                placeholder="ayudar=Ayudar usuarios&#10;moderar=Moderar chat&#10;eventos=Apoyar eventos"
            >{{ old('options_text', $question ? $optionsText($question->options) : '') }}</x-lumoryx.textarea>
            <p class="-mt-2 text-xs leading-5 text-slate-500">Solo se usa si elegiste "Seleccion". Una opcion por linea.</p>

            <x-lumoryx.input
                name="key"
                label="Identificador interno opcional"
                value="{{ $questionKey }}"
                placeholder="se genera automaticamente"
            />

            <x-lumoryx.textarea
                name="rules_text"
                label="Validacion opcional"
                rows="4"
                placeholder="required&#10;string&#10;min:20&#10;max:2500"
            >{{ old('rules_text', $question ? $rulesText($question->rules) : '') }}</x-lumoryx.textarea>
            <p class="-mt-2 text-xs leading-5 text-slate-500">Puedes dejarlo vacio. El sistema aplicara reglas seguras segun el tipo.</p>
        </div>
    </details>

    <div class="flex justify-end">
        <button class="lumoryx-button-primary" type="submit">{{ $submitLabel }}</button>
    </div>
</div>
