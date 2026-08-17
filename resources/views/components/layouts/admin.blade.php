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
        class="lumoryx-bg min-vh-100 overflow-x-hidden"
        x-data="{ sidebar: false }"
        style="--lumoryx-sidebar-bg-image: url('{{ asset($sidebarBackground) }}');"
    >
        <div class="lumoryx-shell max-w-none">
            <aside class="lumoryx-sidebar d-flex flex-column" :class="{ 'translate-x-0': sidebar }">
                <div class="d-flex align-items-center justify-content-between">
                    <x-lumoryx.brand />
                    <button class="lumoryx-button-secondary px-3 py-2 d-lg-none" type="button" @click="sidebar = false">Cerrar</button>
                </div>

                <p class="mt-9 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu</p>
                <nav class="mt-4 flex-1 space-y-2 overflow-y-auto pr-1">
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M4 19V10M12 19V5M20 19v-7'/></svg>" :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M7 3h7l4 4v14H7V3Z'/><path d='M14 3v5h5M10 12h5M10 16h5'/></svg>" :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')">Postulaciones</x-lumoryx.sidebar-link>
                    <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><rect x='4' y='5' width='16' height='15' rx='2'/><path d='M8 3v4M16 3v4M4 10h16'/></svg>" :href="route('admin.interviews.index')" :active="request()->routeIs('admin.interviews.*')">Entrevistas</x-lumoryx.sidebar-link>
                    @if (auth()->user()->isOwner())
                        <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2'/><circle cx='10' cy='7' r='4'/><path d='M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'/></svg>" :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Usuarios</x-lumoryx.sidebar-link>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><path d='M12 2l2.9 6.3 6.9.8-5 4.9 1.2 6.9L12 17.8 5.9 21l1.2-6.9-5-4.9 6.9-.8L12 2Z'/></svg>" :href="route('admin.selected.index')" :active="request()->routeIs('admin.selected.*')">Seleccionados</x-lumoryx.sidebar-link>
                        <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><rect x='3' y='3' width='7' height='7' rx='1'/><rect x='14' y='3' width='7' height='7' rx='1'/><rect x='3' y='14' width='7' height='7' rx='1'/><rect x='14' y='14' width='7' height='7' rx='1'/></svg>" :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">Categorias</x-lumoryx.sidebar-link>
                    @endif
                    @if (auth()->user()->isOwner())
                        <x-lumoryx.sidebar-link icon="<svg viewBox='0 0 24 24'><circle cx='12' cy='12' r='3'/><path d='M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z'/></svg>" :href="route('admin.settings.edit')" :active="request()->routeIs('admin.settings.*')">Ajustes</x-lumoryx.sidebar-link>
                    @endif
                </nav>
                <div class="flex-shrink-0 pt-5">
                    <x-lumoryx.user-chip :user="auth()->user()" subtitle="Administrador" class="w-100" />
                </div>
            </aside>

            <div class="d-flex min-w-0 flex-1 flex-column lg:pl-72">
                <header class="position-sticky top-0 z-30 border-b border-white/10 bg-graphite-950/70 px-4 py-3 backdrop-blur d-lg-none">
                    <button class="lumoryx-button-secondary" type="button" @click="sidebar = true">Menu</button>
                </header>
                <main class="mx-auto w-100 max-w-7xl flex-1 px-4 py-7 sm:px-8 lg:px-10">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-lumoryx.confirm-dialog />
    </div>
</body>
</html>
