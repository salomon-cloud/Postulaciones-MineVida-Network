@php
    $brandName = config('app.name', 'MineVida Network');
    $serverIp = config('community.server_ip', 'play.minevida.net');
    $serverVersion = config('community.server_version', 'Java 1.20+');
    $discordWidgetId = config('community.discord_widget_id');
    $discordWidgetUrl = filled($discordWidgetId) ? 'https://discord.com/widget?id='.$discordWidgetId.'&theme=dark' : null;
    $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
    $rules = [
        ['icon' => '01', 'title' => 'Lee cada pregunta', 'body' => 'Responde con calma y evita enviar informacion incompleta.'],
        ['icon' => '02', 'title' => 'Se honesto', 'body' => 'El equipo revisa experiencia, actitud y disponibilidad real.'],
        ['icon' => '03', 'title' => 'No insistas por DM', 'body' => 'Cualquier avance llegara al panel y por notificacion de Discord.'],
        ['icon' => '04', 'title' => 'Respeta el proceso', 'body' => 'Una mala conducta puede cancelar o pausar tu postulacion.'],
    ];
    $process = [
        ['icon' => 'DS', 'title' => 'Acceso con Discord', 'body' => 'Tu identidad queda vinculada para recibir avisos y seguir el proceso.'],
        ['icon' => 'FM', 'title' => 'Formulario por fases', 'body' => 'Completa datos, experiencia y preguntas sin sentirlo eterno.'],
        ['icon' => 'RV', 'title' => 'Revision del equipo', 'body' => 'El staff revisa respuestas, historial y disponibilidad.'],
        ['icon' => 'RS', 'title' => 'Resultado claro', 'body' => 'Veras el estado final en tu panel y por Discord cuando aplique.'],
    ];
@endphp

<x-layouts.public title="{{ $brandName }} | Postulaciones">
    <section class="lumoryx-home-hero">
        <div class="lumoryx-page-frame">
            <div
                class="lumoryx-home-hero-layout grid items-start gap-10 pt-10 pb-12 lg:grid-cols-[1.05fr_.95fr] lg:pt-14 lg:pb-16"
                style="min-height: 0 !important; align-items: flex-start !important;"
            >
                <div class="max-w-3xl">
                    <div class="lumoryx-home-eyebrow">
                        <span class="lumoryx-status-dot"></span>
                        {{ $applicationsOpen ? 'Postulaciones abiertas' : 'Postulaciones cerradas' }}
                    </div>

                    <h1 class="mt-7 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                        Postulaciones
                        <span class="lumoryx-home-title-accent">{{ $brandName }}</span>
                    </h1>

                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">
                        Un portal ordenado para entrar al equipo de {{ $brandName }}. Revisa las reglas, elige el area correcta y envia tu solicitud con respuestas claras.
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
                        <a class="lumoryx-home-secondary-action" href="#reglas">
                            <span class="text-amber-100">Reglas</span>
                            <span class="font-black text-white">Ver antes de postular</span>
                        </a>
                    </div>

                    <div class="lumoryx-home-trust-row">
                        <span>Revision por fases</span>
                        <span>Panel de seguimiento</span>
                        <span>Notificaciones Discord</span>
                    </div>
                </div>

                <aside class="lumoryx-home-console" aria-label="Resumen del servidor">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase text-amber-100">Servidor</p>
                            <p class="mt-2 text-2xl font-black text-white">{{ $brandName }}</p>
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
                        <div class="lumoryx-home-console-row">
                            <span>Reglas</span>
                            <strong>Obligatorias</strong>
                        </div>
                    </div>

                    <div class="mt-7 border-t border-white/10 pt-5">
                        <p class="text-sm leading-6 text-slate-300">Tu solicitud queda registrada en el panel. El equipo puede moverla a revision, entrevista o resultado final sin perder historial.</p>
                    </div>

                    <div class="lumoryx-home-console-note">
                        <span>!</span>
                        <p>Las postulaciones se revisan con calma. Envia una sola solicitud por area y espera actualizaciones oficiales.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="lumoryx-home-process-section">
        <div class="lumoryx-page-frame">
            <div class="lumoryx-home-process-panel">
                <div class="lumoryx-home-section-head">
                    <div>
                        <p class="lumoryx-home-section-kicker">Proceso guiado</p>
                        <h2 class="mt-2 text-3xl font-black text-white sm:text-4xl">Todo queda ordenado desde el primer envio</h2>
                    </div>
                    <p class="max-w-xl text-sm leading-6 text-slate-400">
                        El sistema evita formularios confusos y mantiene al usuario informado sin saturar al equipo.
                    </p>
                </div>

                <div class="lumoryx-home-process-grid">
                    @foreach ($process as $item)
                        <article class="lumoryx-home-process-card">
                            <span class="lumoryx-home-process-icon">{{ $item['icon'] }}</span>
                            <div>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['body'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="reglas" class="lumoryx-home-rules-section">
        <div class="lumoryx-page-frame">
            <div class="lumoryx-home-section-head">
                <div>
                    <p class="lumoryx-home-section-kicker">Reglas de postulacion</p>
                    <h2 class="mt-2 text-3xl font-black text-white sm:text-4xl">Antes de enviar tu solicitud</h2>
                </div>
                <x-lumoryx.button href="{{ $postulationsUrl }}" variant="secondary">Ver postulaciones</x-lumoryx.button>
            </div>

            <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($rules as $rule)
                    <article class="lumoryx-home-rule-card">
                        <span class="lumoryx-home-rule-icon">{{ $rule['icon'] }}</span>
                        <h3>{{ $rule['title'] }}</h3>
                        <p>{{ $rule['body'] }}</p>
                    </article>
                @endforeach
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

                    @if ($discordWidgetUrl)
                        <iframe
                            class="lumoryx-discord-widget"
                            src="{{ $discordWidgetUrl }}"
                            width="350"
                            height="500"
                            allowtransparency="true"
                            frameborder="0"
                            sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                            title="Widget de Discord de {{ $brandName }}"
                        ></iframe>
                    @else
                        <div class="lumoryx-discord-widget lumoryx-discord-widget-empty">
                            <span class="lumoryx-footer-social-icon">DC</span>
                            <h3 class="mt-4 text-2xl font-black text-white">Discord pendiente</h3>
                            <p class="mt-2 max-w-sm text-center text-sm leading-6 text-slate-400">
                                Configura COMMUNITY_DISCORD_WIDGET_ID para mostrar el widget publico del servidor.
                            </p>
                            <a class="lumoryx-button-primary mt-5" href="{{ route('login.discord') }}">Iniciar sesion</a>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </section>
</x-layouts.public>
