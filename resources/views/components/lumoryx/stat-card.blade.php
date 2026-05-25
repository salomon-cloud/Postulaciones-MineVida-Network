@props(['label', 'value', 'hint' => null, 'tone' => 'purple', 'icon' => null])

@php
    $tones = [
        'purple' => ['bg' => 'border-amber-300/15', 'bar' => 'bg-amber-300/80', 'text' => 'text-amber-200'],
        'cyan' => ['bg' => 'border-amber-300/15', 'bar' => 'bg-amber-300/80', 'text' => 'text-amber-200'],
        'green' => ['bg' => 'border-emerald-300/15', 'bar' => 'bg-emerald-300/80', 'text' => 'text-emerald-200'],
        'red' => ['bg' => 'border-rose-300/15', 'bar' => 'bg-rose-300/80', 'text' => 'text-rose-200'],
        'blue' => ['bg' => 'border-slate-300/15', 'bar' => 'bg-slate-300/80', 'text' => 'text-slate-200'],
    ][$tone] ?? ['bg' => 'border-amber-300/15', 'bar' => 'bg-amber-300/80', 'text' => 'text-amber-200'];
@endphp

<div {{ $attributes->merge(['class' => 'lumoryx-stat-card '.$tones['bg']]) }}>
    <div class="flex min-w-0 items-center gap-4">
        <div class="lumoryx-icon-tile h-12 w-12 {{ $tones['text'] }} text-base font-black">{{ $icon ?? str($label)->substr(0, 1)->upper() }}</div>
        <div class="min-w-0">
            <p class="text-sm font-black text-white">{{ $label }}</p>
            <p class="truncate text-3xl font-black text-white">{{ $value }}</p>
            @if ($hint)
                <p class="text-xs text-slate-400">{{ $hint }}</p>
            @endif
        </div>
    </div>
    <div class="mt-5 h-1.5 rounded-full bg-white/10">
        <div class="h-full w-2/3 rounded-full {{ $tones['bar'] }}"></div>
    </div>
</div>
