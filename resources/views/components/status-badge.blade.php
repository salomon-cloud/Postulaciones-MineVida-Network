@props(['status'])

@php
    $statusEnum = $status instanceof \App\Enums\ApplicationStatus
        ? $status
        : \App\Enums\ApplicationStatus::tryFrom((string) $status);
@endphp

@if ($statusEnum)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-black shadow-[0_10px_22px_rgba(0,0,0,.18)] ring-1 backdrop-blur '.$statusEnum->badgeClasses()]) }}>
        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
        {{ $statusEnum->label() }}
    </span>
@endif
