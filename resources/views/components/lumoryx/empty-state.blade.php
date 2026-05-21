@props(['title' => 'Sin resultados', 'body' => 'No hay informacion para mostrar.'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-white/10 bg-white/[.025] p-8 text-center']) }}>
    <div class="lumoryx-icon-tile mx-auto h-11 w-11 text-amber-100">L</div>
    <h3 class="mt-4 text-lg font-bold text-white">{{ $title }}</h3>
    <p class="mt-2 text-sm text-slate-400">{{ $body }}</p>
</div>
