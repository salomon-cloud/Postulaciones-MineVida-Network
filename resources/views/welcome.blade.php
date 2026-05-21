@php
    $serverIp = config('community.server_ip', 'play.minevida.net');
    $serverVersion = config('community.server_version', 'Java 1.20+');
    $discordWidgetId = config('community.discord_widget_id', '1422483289767153686');
    $discordWidgetUrl = 'https://discord.com/widget?id='.$discordWidgetId.'&theme=dark';
    $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
@endphp

<x-layouts.public title="{{ config('app.name', 'MineVida Network') }} | Postulaciones">
    <section class="lumoryx-home-hero">
        <div class="lumoryx-page-frame">
            <div
                class="lumoryx-home-hero-layout grid items-start gap-10 pt-7 pb-10 lg:grid-cols-[1.08fr_.92fr] lg:pt-9 lg:pb-14"
                style="min-height: 0 !important; align-items: flex-start !important;"
            >
                <div class="max-w-3xl">
                    <div class="lumoryx-home-eyebrow">
                        <span class="lumoryx-status-dot"></span>
                        {{ $applicationsOpen ? 'Postulaciones abiertas' : 'Postulaciones cerradas' }}
                    </div>

                    <h1 class="mt-7 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                        Postulaciones
                        <span class="lumoryx-home-title-accent">MineVida Network</span>
                    </h1>

                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">
                        Forma parte del equipo que mantiene viva la comunidad. Elige el area correcta y envia tu solicitud con calma.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <x-lumoryx.button class="lumoryx-home-primary-action px-6 py-4 text-base" href="{{ route('dashboard') }}">Ir al panel</x-lumoryx.button>
                        @else
                            <x-lumoryx.button class="lumoryx-home-primary-action px-6 py-4 text-base" href="{{ route('login.discord') }}" variant="discord">
                                <img class="h-5 w-5" src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                                <span>Iniciar sesion con Discord</span>
                            </x-lumoryx.button>
                        @endauth

                        <button class="lumoryx-home-secondary-action" type="button" data-copy-text="{{ $serverIp }}">
                            <span class="text-slate-400">IP</span>
                            <span class="font-black text-white">{{ $serverIp }}</span>
                            <span class="text-amber-100">Copiar</span>
                        </button>
                    </div>
                </div>

                <aside class="lumoryx-home-console" aria-label="Resumen del servidor">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase text-amber-100">Servidor</p>
                            <p class="mt-2 text-2xl font-black text-white">MineVida Network</p>
                        </div>
                        <span class="lumoryx-footer-status {{ $applicationsOpen ? 'is-open' : 'is-closed' }}">
                            {{ $applicationsOpen ? 'Abiertas' : 'Cerradas' }}
                        </span>
                    </div>

                    <div class="mt-7 grid gap-3">
                        <div class="lumoryx-home-console-row">
                            <span>Version</span>
                            <strong>{{ $serverVersion }}</strong>
                        </div>
                        <div class="lumoryx-home-console-row">
                            <span>Acceso</span>
                            <strong>Discord</strong>
                        </div>
                        <div class="lumoryx-home-console-row">
                            <span>Revision</span>
                            <strong>Por etapas</strong>
                        </div>
                    </div>

                    <div class="mt-7 border-t border-white/10 pt-5">
                        <p class="text-sm leading-6 text-slate-300">Tu solicitud queda registrada en el panel y el equipo puede actualizar el estado cuando avance el proceso.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section id="discord" class="lumoryx-home-discord-section">
        <div class="lumoryx-page-frame">
            <div class="lumoryx-discord-compact-layout">
                <div class="max-w-2xl">
                    <p class="lumoryx-home-section-kicker">Discord en vivo</p>
                    <h2 class="mt-2 text-3xl font-black text-white sm:text-4xl">Conecta con la comunidad</h2>
                    <p class="mt-4 text-base leading-7 text-slate-300">
                        Mira el estado del servidor, entra a Discord y mantente pendiente de anuncios, soporte y novedades.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <button class="lumoryx-home-secondary-action" type="button" data-copy-text="{{ $serverIp }}">
                            <span class="text-slate-400">IP</span>
                            <span class="font-black text-white">{{ $serverIp }}</span>
                            <span class="text-amber-100">Copiar</span>
                        </button>
                        <x-lumoryx.button href="{{ $postulationsUrl }}" variant="secondary">Postularme</x-lumoryx.button>
                    </div>
                </div>

                <section class="lumoryx-discord-widget-card" aria-label="Widget de Discord">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase text-amber-100">Servidor de Discord</p>
                            <p class="mt-1 text-sm text-slate-400">Comunidad y soporte del servidor.</p>
                        </div>
                        <span class="lumoryx-footer-social-icon">DC</span>
                    </div>

                    <iframe
                        class="lumoryx-discord-widget"
                        src="{{ $discordWidgetUrl }}"
                        width="350"
                        height="500"
                        allowtransparency="true"
                        frameborder="0"
                        sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                        title="Widget de Discord de MineVida Network"
                    ></iframe>
                </section>
            </div>
        </div>
    </section>
</x-layouts.public>
