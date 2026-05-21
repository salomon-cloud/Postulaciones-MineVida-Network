@props(['items'])

@php
    $toneClasses = [
        'amber' => 'border-amber-300/30 bg-amber-300/10 text-amber-100',
        'emerald' => 'border-emerald-300/30 bg-emerald-300/10 text-emerald-100',
        'rose' => 'border-rose-300/30 bg-rose-300/10 text-rose-100',
        'sky' => 'border-sky-300/30 bg-sky-300/10 text-sky-100',
        'discord' => 'border-[#5865F2]/35 bg-[#5865F2]/15 text-indigo-100',
        'slate' => 'border-slate-300/20 bg-slate-300/10 text-slate-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @forelse ($items as $item)
        @php($classes = $toneClasses[$item['tone'] ?? 'slate'] ?? $toneClasses['slate'])
        <article class="relative pl-8">
            @unless ($loop->last)
                <div class="absolute left-3 top-8 h-[calc(100%+1rem)] w-px bg-white/10"></div>
            @endunless
            <div class="absolute left-0 top-1 grid h-7 w-7 place-items-center rounded-md border text-[10px] font-black {{ $classes }}">
                {{ strtoupper(substr($item['icon'] ?? 'i', 0, 2)) }}
            </div>
            <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                <div class="flex min-w-0 flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h3 class="lumoryx-break font-black text-white">{{ $item['title'] }}</h3>
                        <p class="lumoryx-break mt-1 text-sm leading-6 text-slate-400">{{ $item['body'] }}</p>
                    </div>
                    <time class="shrink-0 text-xs font-semibold text-slate-500">
                        {{ $item['time']?->format('d/m/Y H:i') }}
                    </time>
                </div>
                @if (! empty($item['actor']))
                    <p class="mt-3 text-xs text-slate-500">Por {{ $item['actor'] }}</p>
                @endif
            </div>
        </article>
    @empty
        <x-lumoryx.empty-state title="Sin historial" body="Cuando exista movimiento en esta postulacion aparecera aqui." />
    @endforelse
</div>
