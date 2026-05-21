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
    <div class="lumoryx-public-bg min-h-screen overflow-x-hidden">
        <header class="lumoryx-public-header">
            <div class="lumoryx-page-frame flex items-center justify-between py-4">
                <x-lumoryx.brand />
                <x-lumoryx.navbar>
                    <a class="lumoryx-public-nav-link lumoryx-public-nav-link-active" href="{{ route('home') }}">Inicio</a>
                    <a class="lumoryx-public-nav-link" href="{{ route('applications.create') }}">Postulaciones</a>
                    <a class="lumoryx-public-nav-link" href="#discord">Discord</a>
                </x-lumoryx.navbar>
                @auth
                    <x-lumoryx.button class="hidden sm:inline-flex" href="{{ route('dashboard') }}" variant="secondary">Ir al panel</x-lumoryx.button>
                @else
                    <x-lumoryx.button class="hidden sm:inline-flex" href="{{ route('login.discord') }}" variant="secondary">Iniciar sesion</x-lumoryx.button>
                @endauth
            </div>
        </header>

        <main class="lumoryx-public-main">
            <x-flash />
            {{ $slot }}
        </main>

        @php
            $serverIp = config('community.server_ip', 'play.minevida.net');
            $serverVersion = config('community.server_version', 'Java 1.20+');
            $socialLinks = collect(config('community.social_links', []))
                ->filter(fn ($link) => filled($link['url'] ?? null))
                ->values();
            $applicationsOpen = \App\Models\Setting::bool('applications_open', true);
            $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
        @endphp

        <footer id="soporte" class="lumoryx-public-footer">
            <div class="lumoryx-page-frame">
                <div class="lumoryx-footer-minimal">
                    <div class="min-w-0">
                        <x-lumoryx.brand />
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400">
                            Comunidad, postulaciones y soporte conectados en un solo lugar.
                        </p>
                    </div>

                    <div class="flex flex-col gap-4 lg:items-end">
                        <div class="lumoryx-footer-ipbar">
                            <span class="text-xs font-black uppercase text-amber-100">IP</span>
                            <code class="lumoryx-footer-ip">{{ $serverIp }}</code>
                            <button class="lumoryx-footer-copy" type="button" data-copy-text="{{ $serverIp }}">Copiar</button>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @foreach ($socialLinks as $link)
                                <a class="lumoryx-footer-social" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                                    <span class="lumoryx-footer-social-icon">{{ $link['abbr'] }}</span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="lumoryx-footer-bottom">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'MineVida Network') }}. Todos los derechos reservados.</p>
                    <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                        <span>{{ $serverVersion }}</span>
                        <span class="hidden h-1 w-1 rounded-full bg-slate-600 sm:block"></span>
                        <span>{{ $applicationsOpen ? 'Postulaciones abiertas' : 'Postulaciones cerradas' }}</span>
                    </div>
                </div>
            </div>
        </footer>
        <x-lumoryx.confirm-dialog />
    </div>
</body>
</html>
