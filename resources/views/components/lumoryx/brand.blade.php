@props(['large' => false])

@php
    $brandName = config('app.name', 'MineVida Network');
    $logoPath = config('community.logo_path', 'images/MineVidaLogo.png');
@endphp

<a
    href="{{ auth()->check() ? route('dashboard') : route('home') }}"
    {{ $attributes->merge(['class' => 'lumoryx-brand-logo-link']) }}
    aria-label="{{ $brandName }}"
>
    <img
        class="lumoryx-brand-logo {{ $large ? 'lumoryx-brand-logo-large' : '' }}"
        src="{{ asset($logoPath) }}"
        alt="{{ $brandName }}"
        loading="eager"
    >
</a>
