@php
    $brandName = config('app.name', 'MineVida Network');
    $serverIp = config('community.server_ip', 'play.minevida.net');
    $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
@endphp

<x-layouts.public :title="$brandName.' | Postulaciones'" :compact="true">
    <section id="inicio" class="lumoryx-landing">
        <div class="lumoryx-landing-body lumoryx-page-frame">
            <div class="lumoryx-landing-content">
                <div class="lumoryx-landing-eyebrow">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3.5 14.2 8l4.5 2.2-4.5 2.2L12 17l-2.2-4.6-4.5-2.2L9.8 8 12 3.5Z" />
                    </svg>
                    <span>{{ $applicationsOpen ? 'Unete a nuestro equipo' : 'Convocatoria en pausa' }}</span>
                </div>

                <h1 class="lumoryx-landing-title">
                    <span>Postulaciones</span>
                    <strong>{{ $brandName }}</strong>
                </h1>

                <p class="lumoryx-landing-lead">
                    Forma parte del equipo que mantiene y mejora<br class="hidden sm:block">
                    nuestra comunidad cada dia.
                </p>

                <div class="lumoryx-landing-actions">
                    <a class="lumoryx-landing-button lumoryx-landing-button-primary" href="{{ $postulationsUrl }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 3h7l4 4v14H7V3Z" />
                            <path d="M14 3v5h5M10 12h5M10 16h5" />
                        </svg>
                        <span>{{ $applicationsOpen ? 'Ver postulaciones' : 'Consultar estado' }}</span>
                    </a>

                    @auth
                        <a class="lumoryx-landing-button lumoryx-landing-button-secondary" href="{{ route('dashboard') }}">
                            <span class="lumoryx-landing-panel-icon">P</span>
                            <span>Ir a mi panel</span>
                        </a>
                    @else
                        <a class="lumoryx-landing-button lumoryx-landing-button-secondary" href="{{ route('login.discord') }}">
                            <img src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                            <span>Iniciar sesion con Discord</span>
                        </a>
                    @endauth
                </div>

                <div class="lumoryx-landing-features">
                    <article id="reglas" class="lumoryx-landing-feature">
                        <svg class="lumoryx-landing-feature-icon" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M24 7v34M14 12h20M12 12 5 27h14L12 12Zm24 0-7 15h14l-7-15ZM7 31h10M31 31h10M17 41h14" />
                        </svg>
                        <div>
                            <h2>Proceso justo</h2>
                            <p>Evaluamos cada solicitud de manera objetiva y clara.</p>
                        </div>
                    </article>

                    <article id="discord" class="lumoryx-landing-feature">
                        <svg class="lumoryx-landing-feature-icon" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M17 24a7 7 0 1 0 0-14 7 7 0 0 0 0 14Zm14-2a6 6 0 1 0 0-12M4 40c0-8 5-13 13-13s13 5 13 13M29 27c8 0 13 5 13 13" />
                        </svg>
                        <div>
                            <h2>Comunidad activa</h2>
                            <p>Conectate con jugadores y staff en nuestro Discord.</p>
                        </div>
                    </article>

                    <article class="lumoryx-landing-feature">
                        <svg class="lumoryx-landing-feature-icon" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M7 38V26h8v12H7Zm13 0V18h8v20h-8Zm13 0V10h8v28h-8ZM7 18l10-8 8 6L40 4M34 4h6v6" />
                        </svg>
                        <div>
                            <h2>Crecimiento constante</h2>
                            <p>Trabaja en un ambiente divertido y profesional.</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <div class="lumoryx-landing-strip">
            <div class="lumoryx-page-frame lumoryx-landing-strip-inner">
                <button type="button" data-copy-text="{{ $serverIp }}">
                    <span class="lumoryx-landing-strip-icon" aria-hidden="true">◆</span>
                    <strong>{{ $serverIp }}</strong>
                </button>
                <span><i aria-hidden="true">☆</i> Comunidad segura</span>
                <span><i aria-hidden="true">◷</i> Soporte 24/7</span>
                <span><i aria-hidden="true">◌</i> Actualizaciones constantes</span>
            </div>
        </div>
    </section>
</x-layouts.public>
