@props(['kicker', 'title', 'description' => null, 'glow' => 'amber', 'glow2' => 'sky'])

@php
    $glowMap = [
        'amber' => 'bg-amber-300/10',
        'sky' => 'bg-sky-300/10',
        'emerald' => 'bg-emerald-300/10',
        'violet' => 'bg-violet-300/10',
        'rose' => 'bg-rose-300/10',
        'slate' => 'bg-slate-300/10',
    ];
    $glowSoftMap = [
        'amber' => 'bg-amber-300/5',
        'sky' => 'bg-sky-300/5',
        'emerald' => 'bg-emerald-300/5',
        'violet' => 'bg-violet-300/5',
        'rose' => 'bg-rose-300/5',
        'slate' => 'bg-slate-300/5',
    ];
    $glowClass = $glowMap[$glow] ?? $glowMap['amber'];
    $glow2Class = $glowSoftMap[$glow2] ?? $glowSoftMap['sky'];
@endphp

<section class="lumoryx-panel overflow-hidden">
    <div class="position-relative p-5 sm:p-7">
        <div class="pointer-events-none position-absolute inset-0 opacity-80">
            <div class="position-absolute -right-24 -top-24 h-64 w-64 rounded-full {{ $glowClass }} blur-3xl"></div>
            <div class="position-absolute bottom-0 left-1/3 h-40 w-72 rounded-full {{ $glow2Class }} blur-3xl"></div>
        </div>

        <div class="position-relative d-flex flex-column gap-4 flex-lg-row align-items-lg-end justify-content-lg-between">
            <div class="min-w-0">
                <p class="lumoryx-kicker">{{ $kicker }}</p>
                <h1 class="lumoryx-title mt-2">{{ $title }}</h1>
                @if ($description)
                    <p class="mt-3 max-w-3xl text-base leading-7 text-slate-400">{{ $description }}</p>
                @endif
            </div>

            @if (trim($slot) !== '')
                <div class="d-flex flex-shrink-0 flex-column gap-3 flex-sm-row align-items-sm-center">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>

    @isset($stats)
        <div class="d-grid border-t border-white/10 grid-cols-sm-2 grid-cols-lg-3 grid-cols-xxl-6">
            {{ $stats }}
        </div>
    @endisset
</section>
