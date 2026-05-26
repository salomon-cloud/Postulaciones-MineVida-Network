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
                    <x-lumoryx.sidebar-link icon="IN" :href="route('dashboard')" :active="request()->routeIs('dashboard')">Inicio</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="MP" :href="route('applications.index')" :active="request()->routeIs('applications.index', 'applications.show')" :badge="$applicationCount ?: null">Mis postulaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="PO" :href="route('applications.create')" :active="request()->routeIs('applications.create', 'applications.create.type')">Postulaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="NO" :href="route('user.notifications')" :active="request()->routeIs('user.notifications')">Notificaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="PF" :href="route('user.profile')" :active="request()->routeIs('user.profile')">Perfil</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="AJ" :href="route('user.settings')" :active="request()->routeIs('user.settings')">Ajustes</x-lumoryx.sidebar-link>
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
