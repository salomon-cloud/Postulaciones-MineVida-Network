@props(['user' => auth()->user(), 'subtitle' => null])

@php
    $displayName = $user?->discord_global_name ?: $user?->name ?: 'Usuario';
    $roleLabel = $subtitle ?? $user?->role->label();
    $initial = str($displayName)->substr(0, 1)->upper();
@endphp

<div {{ $attributes->merge(['class' => 'lumoryx-user-chip']) }}>
    @if ($user?->discordAvatarUrl())
        <span class="lumoryx-user-avatar-shell">
            <img class="lumoryx-user-avatar" src="{{ $user->discordAvatarUrl() }}" alt="">
            <span class="lumoryx-user-presence"></span>
        </span>
    @else
        <span class="lumoryx-user-avatar-shell">
            <span class="lumoryx-user-avatar-fallback">{{ $initial }}</span>
            <span class="lumoryx-user-presence"></span>
        </span>
    @endif
    <span class="min-w-0 text-left">
        <span class="lumoryx-user-name">{{ $displayName }}</span>
        <span class="lumoryx-user-meta">
            <span class="lumoryx-user-meta-dot"></span>
            {{ $roleLabel }}
        </span>
    </span>
</div>
