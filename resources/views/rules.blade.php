@php
    $brandName = config('app.name', 'MineVida Network');
    $postulationsUrl = auth()->check() ? route('applications.create') : route('login.discord');
    $metaDescription = 'Reglas y requisitos para postularte a '.$brandName.': edad minima, cooldown, cuenta de Discord y normas de conducta.';

    $discordEntry = collect(config('community.social_links', []))
        ->first(fn (array $link) => str($link['label'] ?? '')->lower()->contains('discord'));
    $discordUrl = filled($discordEntry['url'] ?? null) ? $discordEntry['url'] : null;

    $categoryAgeOverrides = $categories->filter(fn ($category) => $category->minimum_age && $category->minimum_age != $minimumAge);
@endphp

<x-layouts.public :title="'Reglas y requisitos | '.$brandName" :description="$metaDescription">
    <section class="lumoryx-landing-section" style="border-top: none;">
        <div class="lumoryx-landing-section-inner">
            <div class="lumoryx-landing-section-head">
                <span class="lumoryx-landing-kicker">Antes de postularte</span>
                <h1 class="lumoryx-landing-section-title text-3xl sm:text-4xl">Reglas y requisitos</h1>
                <p class="lumoryx-landing-section-lead">
                    Esto es lo minimo que necesitas cumplir y respetar para postularte a {{ $brandName }}. Revisalo con calma antes de enviar tu solicitud.
                </p>
            </div>

            <div class="lumoryx-landing-requirements">
                <div class="lumoryx-landing-requirement">
                    <span class="lumoryx-landing-requirement-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v16M4 12h16" /></svg>
                    </span>
                    <div>
                        <h3>Edad minima</h3>
                        <p>Debes tener al menos {{ $minimumAge }} anos para postularte a cualquier area.</p>
                    </div>
                </div>

                <div class="lumoryx-landing-requirement">
                    <span class="lumoryx-landing-requirement-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v6c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V7l-8-4Z" /></svg>
                    </span>
                    <div>
                        <h3>Respeto ante todo</h3>
                        <p>Cero tolerancia a trampas, hacks o insultos hacia jugadores y staff, dentro y fuera del servidor.</p>
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

            @if ($categoryAgeOverrides->isNotEmpty())
                <div class="mx-auto mt-8 max-w-2xl">
                    <p class="text-center text-xs font-black uppercase tracking-wide text-slate-500">Edades minimas por area</p>
                    <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                        @foreach ($categoryAgeOverrides as $category)
                            <span class="lumoryx-landing-category-badge">{{ $category->name }}: {{ $category->minimum_age }}+ anos</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="lumoryx-landing-section lumoryx-landing-section-alt">
        <div class="lumoryx-landing-section-inner">
            <div class="lumoryx-landing-section-head">
                <span class="lumoryx-landing-kicker">Normas de conducta</span>
                <h2 class="lumoryx-landing-section-title">Como esperamos que te comportes</h2>
                <p class="lumoryx-landing-section-lead">Estas normas aplican a todos los miembros, sean staff o no. El incumplimiento puede afectar tu proceso o tu estadia en la comunidad.</p>
            </div>

            <div class="mx-auto mt-10 d-grid max-w-3xl gap-3">
                @foreach ([
                    'Respeta a jugadores y staff. Los insultos, el acoso o la discriminacion no se toleran.',
                    'No uses hacks, trampas, exploits o cualquier ventaja injusta dentro del servidor.',
                    'No abuses de cuentas multiples para manipular sorteos, votaciones o el proceso de postulacion.',
                    'Sigue las indicaciones del staff durante entrevistas, eventos y moderacion.',
                    'La informacion que envies en tu postulacion debe ser real y verificable.',
                    'Las reglas del servidor de Discord aplican igual que las del juego.',
                ] as $rule)
                    <div class="d-flex align-items-start gap-3 rounded-lg border border-white/10 bg-white/[.03] p-4">
                        <span class="mt-0.5 d-grid h-6 w-6 flex-shrink-0 place-items-center rounded-full border border-amber-300/25 bg-amber-300/10 text-xs font-black text-amber-200">✓</span>
                        <p class="text-sm leading-6 text-slate-300">{{ $rule }}</p>
                    </div>
                @endforeach
            </div>

            @if ($discordUrl)
                <p class="lumoryx-landing-section-footnote">
                    Este es el resumen. El reglamento oficial y completo, con todos los detalles, vive en <a href="{{ $discordUrl }}" target="_blank" rel="noopener noreferrer">nuestro Discord</a>.
                </p>
            @else
                <p class="lumoryx-landing-section-footnote">
                    Este es el resumen de las normas principales. Si tienes dudas, el equipo puede resolverlas por Discord una vez que inicies sesion.
                </p>
            @endif
        </div>
    </section>

    <section class="lumoryx-landing-section">
        <div class="lumoryx-landing-section-inner">
            <div class="lumoryx-landing-discord-band" style="border-color: rgba(250, 204, 21, 0.28); background: linear-gradient(135deg, rgba(250, 204, 21, 0.12), rgba(250, 204, 21, 0.02));">
                <div class="lumoryx-landing-discord-icon" style="border-color: rgba(250, 204, 21, 0.35); background: linear-gradient(160deg, rgba(250, 204, 21, 0.3), rgba(250, 204, 21, 0.06));">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-8 w-8 text-amber-100" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l4 4v14H7V3Z" /><path d="M14 3v5h5M10 12h5M10 16h5" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="lumoryx-landing-section-title text-start">Ya revisaste las reglas?</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-400 sm:text-base">Si cumples los requisitos, el siguiente paso es elegir un area y enviar tu postulacion.</p>
                </div>
                <div class="d-flex w-100 flex-column gap-3 sm:w-auto flex-sm-row">
                    <a class="lumoryx-landing-button lumoryx-landing-button-primary" href="{{ $postulationsUrl }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l4 4v14H7V3Z" /><path d="M14 3v5h5M10 12h5M10 16h5" /></svg>
                        <span>Ver postulaciones</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
