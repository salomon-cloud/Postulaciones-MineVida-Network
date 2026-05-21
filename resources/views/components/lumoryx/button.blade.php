@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $class = match ($variant) {
        'secondary' => 'lumoryx-button-secondary',
        'danger' => 'lumoryx-button-danger',
        'success' => 'lumoryx-button-success',
        'blue' => 'lumoryx-button-blue',
        'cyan' => 'lumoryx-button-cyan',
        'purple' => 'lumoryx-button-purple',
        'discord' => 'lumoryx-button-discord',
        default => 'lumoryx-button-primary',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</button>
@endif
