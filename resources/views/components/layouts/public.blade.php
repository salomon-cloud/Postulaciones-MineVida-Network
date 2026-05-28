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
        $publicBackground = config('community.public_background_path', 'images/lumo_fondo.png');
        $serverIp = config('community.server_ip', 'play.minevida.net');
        $serverVersion = config('community.server_version', 'Java 1.20+');
        $socialLinks = collect(config('community.social_links', []))
            ->filter(fn ($link) => filled($link['url'] ?? null))
            ->values();
        $discordLink = $socialLinks->first(fn ($link) => str($link['label'] ?? '')->lower()->contains('discord'));
        $discordUrl = $discordLink['url'] ?? '#discord';
        $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
        $panelUrl = auth()->check() ? route('dashboard') : route('login.discord');

        $applicationsOpen = true;
        $acceptedApplications = collect();

        try {
            $applicationsOpen = \App\Models\Setting::bool('applications_open', true);
            $acceptedApplications = \App\Models\Application::query()
                ->with('user')
                ->where('status', \App\Enums\ApplicationStatus::Accepted->value)
                ->latest('reviewed_at')
                ->latest('updated_at')
                ->take(3)
                ->get();
        } catch (\Throwable $exception) {
            report($exception);
        }

        $navigationLinks = [
            ['label' => 'Inicio', 'href' => route('home'), 'icon' => 'IN'],
            ['label' => 'Postulaciones', 'href' => $postulationsUrl, 'icon' => 'PO'],
            ['label' => 'Discord', 'href' => '#discord', 'icon' => 'DC'],
            ['label' => 'Panel', 'href' => $panelUrl, 'icon' => 'PA'],
        ];
    @endphp
    <div
        class="lumoryx-public-bg min-h-screen overflow-x-hidden"
        style="--lumoryx-public-bg-image: url('{{ asset($publicBackground) }}');"
    >
        <header class="lumoryx-public-header">
            <div class="lumoryx-page-frame flex items-center justify-between py-4">
                <x-lumoryx.brand />
                <x-lumoryx.navbar>
                    <a class="lumoryx-public-nav-link lumoryx-public-nav-link-active" href="{{ route('home') }}">Inicio</a>
                    <a class="lumoryx-public-nav-link" href="{{ route('applications.create') }}">Postulaciones</a>
                    <a class="lumoryx-public-nav-link" href="#reglas">Reglas</a>
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

        <footer id="soporte" class="lumoryx-public-footer">
            <div class="lumoryx-page-frame">
                <div class="lumoryx-footer-grid">
                    <section class="lumoryx-footer-brand">
                        <x-lumoryx.brand />
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400">
                            Comunidad, postulaciones y soporte conectados en un solo lugar. Entra al servidor, revisa tu proceso y mantente cerca del equipo.
                        </p>

                        <div class="mt-6 grid gap-3 sm:max-w-sm">
                            <div class="lumoryx-footer-action-card">
                                <span class="lumoryx-footer-action-icon">IP</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-400">IP del servidor</p>
                                    <code class="lumoryx-footer-ip">{{ $serverIp }}</code>
                                </div>
                                <button class="lumoryx-footer-copy" type="button" data-copy-text="{{ $serverIp }}">Copiar</button>
                            </div>

                            <div class="lumoryx-footer-status-line">
                                <span class="lumoryx-footer-status {{ $applicationsOpen ? 'is-open' : 'is-closed' }}">
                                    {{ $applicationsOpen ? 'Postulaciones abiertas' : 'Postulaciones cerradas' }}
                                </span>
                                <span class="lumoryx-footer-chip">{{ $serverVersion }}</span>
                            </div>
                        </div>
                    </section>

                    <nav class="lumoryx-footer-column" aria-label="Navegacion del footer">
                        <h2 class="lumoryx-footer-heading">Navegacion</h2>
                        <div class="lumoryx-footer-link-list">
                            @foreach ($navigationLinks as $link)
                                <a class="lumoryx-footer-list-link" href="{{ $link['href'] }}">
                                    <span class="lumoryx-footer-list-icon">{{ $link['icon'] }}</span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </nav>

                    <section class="lumoryx-footer-column" aria-label="Redes sociales y comunidad">
                        <h2 class="lumoryx-footer-heading">Comunidad</h2>
                        <div class="lumoryx-footer-social-list">
                            @forelse ($socialLinks as $link)
                                <a class="lumoryx-footer-social-link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                                    <span class="lumoryx-footer-social">{{ $link['abbr'] }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-black text-white">{{ $link['label'] }}</span>
                                        <span class="block truncate text-xs text-slate-500">{{ $link['description'] }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="lumoryx-footer-empty">Configura las redes sociales desde el archivo .env.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="lumoryx-footer-insights">
                        <div class="lumoryx-footer-panel lumoryx-footer-accepted-panel">
                            <div class="lumoryx-footer-panel-head">
                                <h2 class="lumoryx-footer-heading">Ultimos aceptados</h2>
                            </div>

                            <div class="lumoryx-footer-accepted-list">
                                @forelse ($acceptedApplications as $application)
                                    @php
                                        $acceptedUser = $application->user;
                                        $acceptedName = $acceptedUser?->discord_global_name ?: $acceptedUser?->discord_username ?: $application->minecraft_nick ?: 'Usuario';
                                        $acceptedAvatar = $acceptedUser?->discordAvatarUrl();
                                        $acceptedAt = ($application->reviewed_at ?? $application->updated_at)?->diffForHumans() ?? 'reciente';
                                    @endphp

                                    <article class="lumoryx-footer-accepted">
                                        @if ($acceptedAvatar)
                                            <img class="lumoryx-footer-avatar" src="{{ $acceptedAvatar }}" alt="" loading="lazy">
                                        @else
                                            <span class="lumoryx-footer-avatar">{{ str($acceptedName)->substr(0, 2)->upper() }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-white">{{ $acceptedName }}</p>
                                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                                <span class="lumoryx-footer-role-chip">{{ $application->typeLabel() }}</span>
                                                <span class="text-[11px] text-slate-500">Aceptado {{ $acceptedAt }}</span>
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="lumoryx-footer-empty">Aun no hay postulaciones aceptadas para mostrar.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

                <div class="lumoryx-footer-bottom">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'MineVida Network') }}. Todos los derechos reservados.</p>
                    <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                        <span>Sistema de postulaciones</span>
                        <span class="hidden h-1 w-1 rounded-full bg-slate-600 sm:block"></span>
                        <span>Conectado con Discord</span>
                    </div>
                </div>
            </div>
        </footer>
        <x-lumoryx.confirm-dialog />
    </div>
</body>
</html>
