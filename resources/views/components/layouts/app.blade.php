<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'MineVida Network') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="lumoryx-bg min-h-screen overflow-x-hidden" x-data="{ sidebar: false }">
        @guest
            <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                <x-lumoryx.brand />
                <a class="lumoryx-button-secondary" href="{{ route('login.discord') }}">Discord</a>
            </header>
        @endguest

        <div class="lumoryx-shell">
            @auth
            <aside class="lumoryx-sidebar flex flex-col" :class="{ 'translate-x-0': sidebar }">
                <div class="flex items-center justify-between">
                    <x-lumoryx.brand />
                    <button class="lumoryx-button-secondary px-3 py-2 lg:hidden" type="button" @click="sidebar = false">Cerrar</button>
                </div>

                <nav class="mt-8 flex-1 space-y-1 overflow-y-auto pr-1 text-sm">
                        <a class="lumoryx-nav-link {{ request()->routeIs('dashboard') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('dashboard') }}">
                            <span>Dashboard</span>
                        </a>
                        <a class="lumoryx-nav-link {{ request()->routeIs('applications.index', 'applications.show') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('applications.index') }}">
                            <span>Mis postulaciones</span>
                        </a>
                        <a class="lumoryx-nav-link {{ request()->routeIs('applications.create', 'applications.create.type') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('applications.create') }}">
                            <span>Nueva postulacion</span>
                        </a>
                        @if (auth()->user()->isReviewer())
                            <div class="my-4 border-t border-white/10"></div>
                            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Admin</p>
                            <a class="lumoryx-nav-link {{ request()->routeIs('admin.dashboard') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <span>Dashboard</span>
                            </a>
                            <a class="lumoryx-nav-link {{ request()->routeIs('admin.applications.*') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('admin.applications.index') }}">
                                <span>Postulaciones</span>
                            </a>
                            @if (auth()->user()->isOwner())
                                <a class="lumoryx-nav-link {{ request()->routeIs('admin.users.*') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <span>Usuarios</span>
                                </a>
                                <a class="lumoryx-nav-link {{ request()->routeIs('admin.settings.*') ? 'lumoryx-nav-link-active' : '' }}" href="{{ route('admin.settings.edit') }}">
                                    <span>Configuracion</span>
                                </a>
                            @endif
                        @endif
                </nav>

                    <div class="shrink-0 rounded-lg border border-white/10 bg-white/[.06] p-3 pt-3">
                        <div class="flex items-center gap-3">
                            @if (auth()->user()->discordAvatarUrl())
                                <img class="h-10 w-10 rounded-md" src="{{ auth()->user()->discordAvatarUrl() }}" alt="">
                            @else
                            <span class="grid h-10 w-10 place-items-center rounded-md border border-white/10 bg-graphite-850 text-sm font-bold text-amber-100">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-amber-200">{{ auth()->user()->role->label() }}</p>
                            </div>
                        </div>
                        <form class="mt-3" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="lumoryx-button-secondary w-full" type="submit">Cerrar sesion</button>
                        </form>
                    </div>
            </aside>
            @endauth

            <div class="flex min-w-0 flex-1 flex-col @auth lg:pl-72 @endauth">
                <header class="sticky top-0 z-30 border-b border-white/10 bg-graphite-950/75 px-4 py-3 backdrop-blur lg:hidden @guest hidden @endguest">
                    <button class="lumoryx-button-secondary" type="button" @click="sidebar = true">Menu</button>
                </header>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-lumoryx.confirm-dialog />
    </div>
</body>
</html>
