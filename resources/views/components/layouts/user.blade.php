<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'MineVida Network') }}</title>
    <script>
        window.lumoryxConfig = @json(['appName' => config('app.name', 'MineVida Network')]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $applicationCount = auth()->check() ? auth()->user()->applications()->count() : 0;
        $unreadNotifications = auth()->check()
            ? \App\Models\ApplicationLog::query()
                ->visibleToUser()
                ->unread()
                ->whereHas('application', fn ($query) => $query->where('user_id', auth()->id()))
                ->count()
            : 0;
        $sidebarBackground = config('community.sidebar_background_path', 'images/slidebar.png');
    @endphp

    <div
        class="lumoryx-bg min-h-screen overflow-x-hidden"
        x-data="{ sidebar: false }"
        style="--lumoryx-sidebar-bg-image: url('{{ asset($sidebarBackground) }}');"
    >
        <div class="lumoryx-shell max-w-none">
            <aside class="lumoryx-sidebar flex flex-col" :class="{ 'translate-x-0': sidebar }">
                <div class="flex items-center justify-between">
                    <x-lumoryx.brand large />
                    <button class="lumoryx-button-secondary px-3 py-2 lg:hidden" type="button" @click="sidebar = false">Cerrar</button>
                </div>

                <nav class="mt-8 flex-1 space-y-2 overflow-y-auto pr-1">
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M3 11l9-8 9 8M5 10v10h14V10'/></svg>" :href="route('dashboard')" :active="request()->routeIs('dashboard')">Inicio</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M7 3h7l4 4v14H7V3Z'/><path d='M14 3v5h5M10 12h5M10 16h5'/></svg>" :href="route('applications.index')" :active="request()->routeIs('applications.index', 'applications.show')" :badge="$applicationCount ?: null">Mis postulaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M12 5v14M5 12h14'/></svg>" :href="route('applications.create')" :active="request()->routeIs('applications.create', 'applications.create.type')">Postulaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9'/><path d='M13.7 21a2 2 0 0 1-3.4 0'/></svg>" :href="route('user.notifications')" :active="request()->routeIs('user.notifications')" :badge="$unreadNotifications ?: null" badge-alert>Notificaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><circle cx='12' cy='8' r='4'/><path d='M4 21v-1a8 8 0 0 1 16 0v1'/></svg>" :href="route('user.profile')" :active="request()->routeIs('user.profile')">Perfil</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><circle cx='12' cy='12' r='3'/><path d='M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z'/></svg>" :href="route('user.settings')" :active="request()->routeIs('user.settings')">Ajustes</x-lumoryx.sidebar-link>
                </nav>

                <div class="shrink-0 space-y-4 pt-5">
                    <div class="rounded-lg border border-white/10 bg-white/[.035] p-5">
                        <p class="text-sm font-semibold text-white">Gracias por ser parte de {{ config('app.name', 'MineVida Network') }}</p>
                        <p class="mt-2 text-xs leading-5 text-slate-400">Tu comunidad, tu aventura.</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="lumoryx-button-secondary w-full" type="submit">Cerrar sesion</button>
                    </form>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-white/10 bg-graphite-950/70 px-4 py-3 backdrop-blur lg:hidden">
                    <button class="lumoryx-button-secondary" type="button" @click="sidebar = true">Menu</button>
                </header>
                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-7 sm:px-8 lg:px-10">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-lumoryx.confirm-dialog />
    </div>
</body>
</html>
