@props([
    'title' => 'Sin resultados',
    'body' => 'No hay informacion para mostrar.',
    'tone' => 'slate',
])

@php
    $tones = [
        'slate' => 'lumoryx-empty-icon-slate',
        'amber' => 'lumoryx-empty-icon-amber',
        'emerald' => 'lumoryx-empty-icon-emerald',
        'sky' => 'lumoryx-empty-icon-sky',
    ];
    $toneClass = $tones[$tone] ?? $tones['slate'];
@endphp

<div {{ $attributes->merge(['class' => 'lumoryx-empty-state']) }}>
    <span class="lumoryx-empty-icon {{ $toneClass }}">
        @isset($icon)
            {{ $icon }}
        @else
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="16" rx="2" />
                <path d="M3 10h18M8 3v4M16 3v4" />
            </svg>
        @endisset
    </span>
    <h3>{{ $title }}</h3>
    <p>{{ $body }}</p>

    @if (trim($slot) !== '')
        <div class="lumoryx-empty-actions">{{ $slot }}</div>
    @endif
</div>
