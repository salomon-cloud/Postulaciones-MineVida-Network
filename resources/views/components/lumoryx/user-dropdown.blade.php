@props(['user' => auth()->user()])

@php
    $displayName = $user?->discord_global_name ?: $user?->name ?: 'Usuario';
    $roleLabel = $user?->role->label();
    $initial = str($displayName)->substr(0, 1)->upper();
@endphp

<div x-data="{ open: false }" class="relative">
    <button class="lumoryx-user-chip lumoryx-user-chip-dropdown" type="button" @click="open = ! open" :aria-expanded="open.toString()">
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
        <span class="lumoryx-user-chevron" aria-hidden="true"></span>
    </button>
    <div class="lumoryx-user-menu" x-show="open" x-cloak @click.outside="open = false">
        <a class="lumoryx-nav-link" href="{{ route('user.profile') }}">Perfil</a>
        <a class="lumoryx-nav-link" href="{{ route('user.notifications') }}">Notificaciones</a>
        <a class="lumoryx-nav-link" href="{{ route('user.settings') }}">Ajustes</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="lumoryx-nav-link w-full" type="submit">Cerrar sesion</button>
        </form>
    </div>
</div>
