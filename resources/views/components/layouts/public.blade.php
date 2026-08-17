@props(['compact' => false, 'description' => null, 'ogImage' => null])

@php
    $metaTitle = $title ?? config('app.name', 'MineVida Network');
    $metaDescription = $description ?: 'Sistema de postulaciones de '.config('app.name', 'MineVida Network').'. Revisa las areas disponibles, el proceso de seleccion y postulate para unirte al equipo.';
    $metaImage = $ogImage ?: asset(config('community.public_background_path', 'images/lumo_fondo.png'));
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#050506">
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="icon" type="image/png" href="{{ asset(config('community.logo_path', 'images/MineVidaLogo.png')) }}">
    <title>{{ $metaTitle }}</title>

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name', 'MineVida Network') }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="es_MX">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

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
        $discordUrl = $discordLink['url'] ?? route('home').'#discord';
        $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
        $panelUrl = auth()->check() ? route('dashboard') : route('login.discord');

        $applicationsOpen = true;
        $acceptedApplications = collect();

        try {
            $applicationsOpen = \App\Models\Setting::bool('applications_open', true);
            if (! $compact) {
                $acceptedApplications = \App\Models\Application::query()
                    ->with('user')
                    ->where('status', \App\Enums\ApplicationStatus::Accepted->value)
                    ->latest('reviewed_at')
                    ->latest('updated_at')
                    ->take(3)
                    ->get();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $navigationLinks = [
            ['label' => 'Inicio', 'href' => route('home'), 'icon' => 'IN'],
            ['label' => 'Postulaciones', 'href' => $postulationsUrl, 'icon' => 'PO'],
            ['label' => 'Reglas', 'href' => route('rules'), 'icon' => 'RG'],
            ['label' => 'Discord', 'href' => '#discord', 'icon' => 'DC'],
            ['label' => 'Panel', 'href' => $panelUrl, 'icon' => 'PA'],
        ];
    @endphp
    <div
        class="lumoryx-public-bg min-vh-100 overflow-x-hidden"
        style="--lumoryx-public-bg-image: url('{{ asset($publicBackground) }}');"
    >
        <header
            class="lumoryx-public-header"
            x-data="{ mobileNavOpen: false, scrolled: false }"
            x-init="scrolled = window.scrollY > 12"
            @scroll.window="scrolled = window.scrollY > 12"
            @keydown.escape.window="mobileNavOpen = false"
            @close-mobile-nav.window="mobileNavOpen = false"
            :class="{ 'is-scrolled': scrolled }"
        >
            <div class="lumoryx-page-frame d-flex align-items-center justify-content-between py-4 transition-[padding]" :class="scrolled ? 'lg:py-2.5' : 'lg:py-4'">
                <x-lumoryx.brand />
                <x-lumoryx.navbar>
                    <a class="lumoryx-public-nav-link {{ request()->routeIs('home') ? 'lumoryx-public-nav-link-active' : '' }}" href="{{ route('home') }}#inicio">Inicio</a>
                    <a class="lumoryx-public-nav-link" href="{{ route('home') }}#categorias">Categorias</a>
                    <a class="lumoryx-public-nav-link" href="{{ $postulationsUrl }}">Postulaciones</a>
                    <a class="lumoryx-public-nav-link {{ request()->routeIs('rules') ? 'lumoryx-public-nav-link-active' : '' }}" href="{{ route('rules') }}">Reglas</a>
                    <a class="lumoryx-public-nav-link" href="{{ $discordUrl }}">Discord</a>
                    <a class="lumoryx-public-nav-link" href="{{ $panelUrl }}">Panel</a>
                </x-lumoryx.navbar>
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <x-lumoryx.button class="lumoryx-public-login-action d-none d-inline-sm-flex" href="{{ route('dashboard') }}">Ir al panel</x-lumoryx.button>
                    @else
                        <x-lumoryx.button variant="discord" class="lumoryx-public-login-action d-none d-inline-sm-flex" href="{{ route('login.discord') }}">
                            <img class="h-4 w-4" src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                            <span>Iniciar sesion</span>
                        </x-lumoryx.button>
                    @endauth

                    <button
                        type="button"
                        class="lumoryx-public-menu-toggle d-lg-none"
                        @click="mobileNavOpen = !mobileNavOpen"
                        :aria-expanded="mobileNavOpen ? 'true' : 'false'"
                        aria-controls="lumoryx-mobile-nav"
                        aria-label="Abrir menu de navegacion"
                    >
                        <svg x-show="!mobileNavOpen" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                        <svg x-show="mobileNavOpen" x-cloak viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
            </div>

            <div
                id="lumoryx-mobile-nav"
                class="lumoryx-public-mobile-nav d-lg-none"
                x-show="mobileNavOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                @click.outside="mobileNavOpen = false"
            >
                <div class="lumoryx-page-frame lumoryx-public-mobile-nav-inner">
                    <a class="lumoryx-public-mobile-link" href="{{ route('home') }}#inicio" @click="mobileNavOpen = false">Inicio</a>
                    <a class="lumoryx-public-mobile-link" href="{{ route('home') }}#categorias" @click="mobileNavOpen = false">Categorias</a>
                    <a class="lumoryx-public-mobile-link" href="{{ $postulationsUrl }}" @click="mobileNavOpen = false">Postulaciones</a>
                    <a class="lumoryx-public-mobile-link" href="{{ route('rules') }}" @click="mobileNavOpen = false">Reglas</a>
                    <a class="lumoryx-public-mobile-link" href="{{ $discordUrl }}" @click="mobileNavOpen = false">Discord</a>
                    <a class="lumoryx-public-mobile-link" href="{{ $panelUrl }}" @click="mobileNavOpen = false">Panel</a>

                    @auth
                        <x-lumoryx.button class="lumoryx-public-mobile-cta" href="{{ route('dashboard') }}">Ir al panel</x-lumoryx.button>
                    @else
                        <x-lumoryx.button variant="discord" class="lumoryx-public-mobile-cta" href="{{ route('login.discord') }}">
                            <img class="h-4 w-4" src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                            <span>Iniciar sesion con Discord</span>
                        </x-lumoryx.button>
                    @endauth
                </div>
            </div>
        </header>

        <main class="lumoryx-public-main">
            <x-flash />
            {{ $slot }}
        </main>

        @unless ($compact)
        <footer id="soporte" class="lumoryx-public-footer">
            <div class="lumoryx-page-frame">
                <div class="lumoryx-footer-grid">
                    <section class="lumoryx-footer-brand">
                        <x-lumoryx.brand />
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400">
                            Comunidad, postulaciones y soporte conectados en un solo lugar. Entra al servidor, revisa tu proceso y mantente cerca del equipo.
                        </p>

                        <div class="mt-6 d-grid gap-3 sm:max-w-sm">
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
                                        <span class="d-block truncate text-sm font-black text-white">{{ $link['label'] }}</span>
                                        <span class="d-block truncate text-xs text-slate-500">{{ $link['description'] }}</span>
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
                                            <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
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
                    <div class="d-flex flex-wrap align-items-center gap-3 justify-content-sm-end">
                        <span>Sistema de postulaciones</span>
                        <span class="d-none h-1 w-1 rounded-full bg-slate-600 d-sm-block"></span>
                        <span>Conectado con Discord</span>
                    </div>
                </div>
            </div>
        </footer>
        @endunless
        <x-lumoryx.confirm-dialog />
    </div>
</body>
</html>
