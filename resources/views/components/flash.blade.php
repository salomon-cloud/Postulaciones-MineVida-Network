@foreach ([
    'success' => 'border-emerald-400/30 bg-emerald-500/10 text-emerald-100',
    'error' => 'border-rose-400/30 bg-rose-500/10 text-rose-100',
    'info' => 'border-sky-400/30 bg-sky-500/10 text-sky-100',
] as $key => $classes)
    @if (session($key))
        <div class="mb-5 rounded-lg border px-4 py-3 text-sm shadow-panel backdrop-blur {{ $classes }}">
            {{ session($key) }}
        </div>
    @endif
@endforeach

@php
    $flashErrors = $errors ?? null;
@endphp

@if ($flashErrors?->any())
    <div class="mb-5 rounded-lg border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100 shadow-panel backdrop-blur">
        <p class="font-semibold">Revisa los campos marcados.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($flashErrors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
