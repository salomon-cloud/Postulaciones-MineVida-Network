<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin | ' . config('app.name', 'MineVida Network') }}</title>
    <script>
        window.lumoryxConfig = @json(['appName' => config('app.name', 'MineVida Network')]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
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
                    <x-lumoryx.brand />
                    <button class="lumoryx-button-secondary px-3 py-2 lg:hidden" type="button" @click="sidebar = false">Cerrar</button>
                </div>

                <p class="mt-9 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu</p>
                <nav class="mt-4 flex-1 space-y-2 overflow-y-auto pr-1">
                    <x-lumoryx.sidebar-link icon="DB" :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="AP" :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')">Postulaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="EN" :href="route('admin.interviews.index')" :active="request()->routeIs('admin.interviews.*')">Entrevistas</x-lumoryx.sidebar-link>
                    @if (auth()->user()->isOwner())
                        <x-lumoryx.sidebar-link icon="US" :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Usuarios</x-lumoryx.sidebar-link>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <x-lumoryx.sidebar-link icon="SE" :href="route('admin.selected.index')" :active="request()->routeIs('admin.selected.*')">Seleccionados</x-lumoryx.sidebar-link>
                        <x-lumoryx.sidebar-link icon="CA" :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">Categorias</x-lumoryx.sidebar-link>
                    @endif
                    @if (auth()->user()->isOwner())
                        <x-lumoryx.sidebar-link icon="AJ" :href="route('admin.settings.edit')" :active="request()->routeIs('admin.settings.*')">Ajustes</x-lumoryx.sidebar-link>
                    @endif
                </nav>
                <div class="shrink-0 pt-5">
                    <x-lumoryx.user-chip :user="auth()->user()" subtitle="Administrador" class="w-full" />
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
