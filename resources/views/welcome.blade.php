@php
    $brandName = config('app.name', 'MineVida Network');
    $serverIp = config('community.server_ip', 'play.minevida.net');
    $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
    $metaDescription = 'Postulate para formar parte del staff de '.$brandName.': moderacion, desarrollo, construccion y multimedia. Proceso claro y conectado a Discord.';
@endphp

<x-layouts.public :title="$brandName.' | Postulaciones'" :description="$metaDescription">
    <section id="inicio" class="lumoryx-landing">
        <div class="lumoryx-landing-body lumoryx-page-frame">
            <div class="lumoryx-landing-content">
                <div class="lumoryx-landing-eyebrow">
                    <span class="lumoryx-landing-status-dot {{ $applicationsOpen ? 'is-open' : 'is-closed' }}" aria-hidden="true"></span>
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
                        <a class="lumoryx-landing-button lumoryx-landing-button-discord" href="{{ route('login.discord') }}">
                            <img src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                            <span>Iniciar sesion con Discord</span>
                        </a>
                    @endauth
                </div>

                @if ($recentAccepted->isNotEmpty())
                    <div class="lumoryx-landing-social-proof">
                        <div class="lumoryx-landing-social-avatars">
                            @foreach ($recentAccepted as $accepted)
                                @php
                                    $acceptedUser = $accepted->user;
                                    $acceptedName = $acceptedUser?->discord_global_name ?: $acceptedUser?->discord_username ?: $accepted->minecraft_nick ?: 'Usuario';
                                    $acceptedAvatar = $acceptedUser?->discordAvatarUrl();
                                @endphp

                                @if ($acceptedAvatar)
                                    <img class="lumoryx-landing-social-avatar" src="{{ $acceptedAvatar }}" alt="" loading="lazy" title="{{ $acceptedName }}">
                                @else
                                    <span class="lumoryx-landing-social-avatar" title="{{ $acceptedName }}">{{ str($acceptedName)->substr(0, 2)->upper() }}</span>
                                @endif
                            @endforeach
                        </div>
                        <span class="lumoryx-landing-social-text">
                            @if ($totalAccepted > 0)
                                Ya somos {{ number_format($totalAccepted) }} en el equipo
                            @else
                                Jugadores aceptados recientemente en el equipo
                            @endif
                        </span>
                    </div>
                @endif

                <div class="lumoryx-landing-features">
                    <article class="lumoryx-landing-feature lumoryx-landing-feature-gold">
                        <span class="lumoryx-landing-feature-icon-wrap">
                            <svg class="lumoryx-landing-feature-icon" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M24 7v34M14 12h20M12 12 5 27h14L12 12Zm24 0-7 15h14l-7-15ZM7 31h10M31 31h10M17 41h14" />
                            </svg>
                        </span>
                        <div>
                            <h2>Proceso justo</h2>
                            <p>Evaluamos cada solicitud de manera objetiva y clara.</p>
                        </div>
                    </article>

                    <article class="lumoryx-landing-feature lumoryx-landing-feature-discord">
                        <span class="lumoryx-landing-feature-icon-wrap">
                            <svg class="lumoryx-landing-feature-icon" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M17 24a7 7 0 1 0 0-14 7 7 0 0 0 0 14Zm14-2a6 6 0 1 0 0-12M4 40c0-8 5-13 13-13s13 5 13 13M29 27c8 0 13 5 13 13" />
                            </svg>
                        </span>
                        <div>
                            <h2>Comunidad activa</h2>
                            <p>Conectate con jugadores y staff en nuestro Discord.</p>
                        </div>
                    </article>

                    <article class="lumoryx-landing-feature lumoryx-landing-feature-green">
                        <span class="lumoryx-landing-feature-icon-wrap">
                            <svg class="lumoryx-landing-feature-icon" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M7 38V26h8v12H7Zm13 0V18h8v20h-8Zm13 0V10h8v28h-8ZM7 18l10-8 8 6L40 4M34 4h6v6" />
                            </svg>
                        </span>
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
                @if ($totalAccepted > 0)
                    <span><i aria-hidden="true">◈</i> {{ number_format($totalAccepted) }}+ miembros aceptados</span>
                @else
                    <span><i aria-hidden="true">◌</i> Actualizaciones constantes</span>
                @endif
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section id="categorias" class="lumoryx-landing-section">
            <div class="lumoryx-landing-section-inner">
                <div class="lumoryx-landing-section-head">
                    <span class="lumoryx-landing-kicker">Areas disponibles</span>
                    <h2 class="lumoryx-landing-section-title">Elige donde quieres aportar</h2>
                    <p class="lumoryx-landing-section-lead">Cada area tiene su propio proceso y preguntas. Postulate a la que mejor se ajuste a ti.</p>
                </div>

                <div class="lumoryx-landing-category-grid">
                    @foreach ($categories as $category)
                        <article class="lumoryx-landing-category-card {{ $category->is_open ? '' : 'is-closed' }}" style="--lumoryx-cat-color: {{ $category->accent_color ?: '#facc15' }};">
                            <div class="lumoryx-landing-category-top">
                                <span class="lumoryx-landing-category-icon">{{ $category->icon ?: str($category->name)->substr(0, 2)->upper() }}</span>
                                @if (! $category->is_open)
                                    <span class="lumoryx-landing-category-badge">Cerrada</span>
                                @elseif ($category->minimum_age)
                                    <span class="lumoryx-landing-category-badge">{{ $category->minimum_age }}+ anos</span>
                                @endif
                            </div>
                            <h3>{{ $category->name }}</h3>
                            <p>{{ $category->summary ?: 'Postulacion para '.$category->name.'.' }}</p>
                            <a class="lumoryx-landing-category-link" href="{{ auth()->check() ? route('applications.create.type', $category->slug) : route('login.discord') }}">
                                <span>{{ $category->is_open ? 'Postularme' : 'Ver detalles' }}</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="proceso" class="lumoryx-landing-section lumoryx-landing-section-alt">
        <div class="lumoryx-landing-section-inner">
            <div class="lumoryx-landing-section-head">
                <span class="lumoryx-landing-kicker">El proceso</span>
                <h2 class="lumoryx-landing-section-title">Como funciona tu postulacion</h2>
                <p class="lumoryx-landing-section-lead">Desde que envias tu solicitud hasta la respuesta final, este es el camino que sigue.</p>
            </div>

            <div class="lumoryx-landing-process">
                @foreach ($processSteps as $index => $step)
                    <div class="lumoryx-landing-process-step">
                        <span class="lumoryx-landing-process-number">{{ $index + 1 }}</span>
                        <div>
                            <h3>{{ $step['label'] }}</h3>
                            <p>{{ $step['summary'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="requisitos" class="lumoryx-landing-section">
        <div class="lumoryx-landing-section-inner">
            <div class="lumoryx-landing-section-head">
                <span class="lumoryx-landing-kicker">Antes de postularte</span>
                <h2 class="lumoryx-landing-section-title">Reglas y requisitos</h2>
                <p class="lumoryx-landing-section-lead">Revisa esto antes de enviar tu solicitud para evitar rechazos innecesarios.</p>
            </div>

            <div class="lumoryx-landing-requirements">
                <div class="lumoryx-landing-requirement">
                    <span class="lumoryx-landing-requirement-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v16M4 12h16" /></svg>
                    </span>
                    <div>
                        <h3>Edad minima</h3>
                        <p>Debes tener al menos {{ $minimumAge }} anos para postularte.</p>
                    </div>
                </div>

                <div class="lumoryx-landing-requirement">
                    <span class="lumoryx-landing-requirement-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v6c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V7l-8-4Z" /></svg>
                    </span>
                    <div>
                        <h3>Respeto ante todo</h3>
                        <p>Cero tolerancia a trampas, hacks o insultos hacia jugadores y staff.</p>
                    </div>
                </div>

                <div class="lumoryx-landing-requirement">
                    <span class="lumoryx-landing-requirement-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8c0-3.9 3.1-7 7-7s7 3.1 7 7" /></svg>
                    </span>
                    <div>
                        <h3>Cuenta de Discord</h3>
                        <p>{{ $requireDiscordGuild ? 'Necesitas Discord para iniciar sesion y unirte a nuestro servidor automaticamente.' : 'Necesitas Discord para iniciar sesion y para que el equipo pueda contactarte.' }}</p>
                    </div>
                </div>

                <div class="lumoryx-landing-requirement">
                    <span class="lumoryx-landing-requirement-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5l3 3M21 12a9 9 0 1 1-9-9" /></svg>
                    </span>
                    <div>
                        <h3>Si no fuiste seleccionado</h3>
                        <p>Puedes volver a postularte despues de {{ $reapplyCooldownDays }} dias.</p>
                    </div>
                </div>
            </div>

            <div class="mx-auto mt-8 flex justify-center">
                <a class="lumoryx-landing-button lumoryx-landing-button-secondary" href="{{ route('rules') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l4 4v14H7V3Z" /><path d="M14 3v5h5M10 12h5M10 16h5" /></svg>
                    <span>Ver reglamento completo</span>
                </a>
            </div>

            @if ($discordUrl)
                <p class="lumoryx-landing-section-footnote">
                    Reglas completas del servidor disponibles en <a href="{{ $discordUrl }}" target="_blank" rel="noopener noreferrer">nuestro Discord</a>.
                </p>
            @endif
        </div>
    </section>

    <section id="discord" class="lumoryx-landing-section lumoryx-landing-section-alt">
        <div class="lumoryx-landing-section-inner">
            <div class="lumoryx-landing-discord-band">
                <div class="lumoryx-landing-discord-icon">
                    <img src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="lumoryx-landing-section-title text-left">Unete a nuestra comunidad de Discord</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-400 sm:text-base">
                        Habla con el equipo, sigue el estado de tu postulacion y entérate primero de anuncios y aperturas de convocatoria.
                    </p>
                </div>
                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    @auth
                        @if ($discordUrl)
                            <a class="lumoryx-landing-button lumoryx-landing-button-discord" href="{{ $discordUrl }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                                <span>Entrar al servidor</span>
                            </a>
                        @endif
                    @else
                        <a class="lumoryx-landing-button lumoryx-landing-button-discord" href="{{ route('login.discord') }}">
                            <img src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                            <span>Iniciar sesion con Discord</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
