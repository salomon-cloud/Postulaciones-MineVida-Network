@php
    $announcementChannels = old('discord_announcement_channel_id', $settings['discord_announcement_channel_id']);
    $selectedChannels = old('discord_selected_channel_id', $settings['discord_selected_channel_id']);
    $systemLogChannels = old('discord_system_log_channel_id', $settings['discord_system_log_channel_id']);
    $rawSystemLogEvents = old('discord_system_log_events', $settings['discord_system_log_events']);
    $systemLogSelectedEvents = collect(is_array($rawSystemLogEvents) ? $rawSystemLogEvents : (preg_split('/[\s,;]+/', (string) $rawSystemLogEvents) ?: []))
        ->filter()
        ->values();
    $channelCount = fn ($value) => collect(preg_split('/[\s,;]+/', (string) $value) ?: [])
        ->filter()
        ->unique()
        ->count();
    $announcementChannelCount = $channelCount($announcementChannels);
    $selectedChannelCount = $channelCount($selectedChannels);
    $systemLogChannelCount = $channelCount($systemLogChannels);
@endphp

<x-layouts.admin :title="'Configuracion | '.config('app.name', 'MineVida Network')">
    <x-lumoryx.page-header kicker="Owner" title="Configuracion" description="Ajusta el flujo del sistema, la conexion con Discord y los canales donde se publican anuncios automaticos." glow="violet" glow2="amber">
        <div class="d-grid gap-3 grid-cols-sm-3 xl:w-[520px]">
            <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Estado</p>
                <p class="mt-2 text-lg font-black {{ old('applications_open', $settings['applications_open']) ? 'text-emerald-200' : 'text-rose-200' }}">
                    {{ old('applications_open', $settings['applications_open']) ? 'Abiertas' : 'Cerradas' }}
                </p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Canales</p>
                <p class="mt-2 text-lg font-black text-white">{{ $announcementChannelCount + $selectedChannelCount + $systemLogChannelCount }}</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Discord</p>
                <p class="mt-2 text-lg font-black {{ old('require_discord_guild', $settings['require_discord_guild']) ? 'text-amber-200' : 'text-slate-300' }}">
                    {{ old('require_discord_guild', $settings['require_discord_guild']) ? 'Verificado' : 'Opcional' }}
                </p>
            </div>
        </div>
    </x-lumoryx.page-header>

    <form class="mt-6 space-y-6" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PATCH')

        {{-- 1. Estado general --}}
        <section class="lumoryx-section-card">
            <div class="lumoryx-section-head">
                <span class="lumoryx-section-step">1</span>
                <div class="min-w-0 flex-grow-1">
                    <h2>Estado general</h2>
                    <p>Controla si el sistema acepta nuevas solicitudes y que requisitos basicos se aplican al usuario.</p>
                </div>
            </div>

            <div class="lumoryx-section-body d-grid gap-4">
                <label class="lumoryx-switch-row">
                    <span class="min-w-0">
                        <span class="lumoryx-switch-title">Postulaciones abiertas</span>
                        <span class="lumoryx-switch-help">Cuando cambies este estado, el sistema puede publicar un aviso en Discord si los anuncios estan activos.</span>
                    </span>
                    <span class="d-flex flex-shrink-0 align-items-center gap-3">
                        <span class="lumoryx-pill {{ old('applications_open', $settings['applications_open']) ? 'lumoryx-pill-emerald' : 'lumoryx-pill-rose' }}">
                            {{ old('applications_open', $settings['applications_open']) ? 'Activas' : 'Pausadas' }}
                        </span>
                        <input class="lumoryx-toggle-checkbox" type="checkbox" name="applications_open" value="1" @checked(old('applications_open', $settings['applications_open']))>
                    </span>
                </label>

                <div class="d-grid gap-4 grid-cols-md-2">
                    <div>
                        <label class="lumoryx-label" for="minimum_age">Edad minima para postular</label>
                        <input class="lumoryx-input mt-2" id="minimum_age" name="minimum_age" type="number" min="10" max="30" value="{{ old('minimum_age', $settings['minimum_age']) }}" required>
                        <p class="mt-2 text-xs text-slate-500">Se muestra en el inicio y valida los formularios.</p>
                        @error('minimum_age')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="lumoryx-label" for="reapply_cooldown_days">Dias antes de volver a postular</label>
                        <input class="lumoryx-input mt-2" id="reapply_cooldown_days" name="reapply_cooldown_days" type="number" min="0" max="365" value="{{ old('reapply_cooldown_days', $settings['reapply_cooldown_days']) }}" required>
                        <p class="mt-2 text-xs text-slate-500">Usa 0 si quieres permitir reintentos sin espera.</p>
                        @error('reapply_cooldown_days')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Acceso Discord --}}
        <section class="lumoryx-section-card">
            <div class="lumoryx-section-head">
                <span class="lumoryx-section-step">2</span>
                <div class="min-w-0 flex-grow-1">
                    <h2>Acceso Discord</h2>
                    <p>Define si el usuario debe pertenecer al servidor antes de usar el sistema.</p>
                </div>
            </div>

            <div class="lumoryx-section-body d-grid gap-4 grid-cols-lg-2">
                <label class="lumoryx-switch-row">
                    <span class="min-w-0">
                        <span class="lumoryx-switch-title">Verificar pertenencia al servidor</span>
                        <span class="lumoryx-switch-help">Requiere scopes de Discord y el ID del servidor configurado en el entorno.</span>
                    </span>
                    <input class="lumoryx-toggle-checkbox" type="checkbox" name="require_discord_guild" value="1" @checked(old('require_discord_guild', $settings['require_discord_guild']))>
                </label>

                <div class="lumoryx-note lumoryx-note-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                    <p>Si esta activo, el login revisa que la cuenta de Discord este dentro del servidor. Si tambien usas <strong>guilds.join</strong>, el bot puede intentar unirlo automaticamente.</p>
                </div>
            </div>
        </section>

        {{-- 3. Anuncios automaticos --}}
        <section class="lumoryx-section-card">
            <div class="lumoryx-section-head">
                <span class="lumoryx-section-step">3</span>
                <div class="min-w-0 flex-grow-1">
                    <h2>Anuncios automaticos</h2>
                    <p>Publica apertura, cierre y seleccionados en uno o varios canales de Discord.</p>
                </div>
                <label class="d-flex flex-shrink-0 align-items-center gap-2.5 text-sm font-black text-white">
                    <span class="d-none d-sm-inline">Activar</span>
                    <input class="lumoryx-toggle-checkbox" type="checkbox" name="discord_announce_applications_window" value="1" @checked(old('discord_announce_applications_window', $settings['discord_announce_applications_window']))>
                </label>
            </div>

            <div class="lumoryx-section-body d-grid gap-4 grid-cols-lg-2">
                <div>
                    <label class="lumoryx-label" for="discord_announcement_channel_id">Canales para apertura y cierre</label>
                    <textarea class="lumoryx-input mt-2 min-h-32" id="discord_announcement_channel_id" name="discord_announcement_channel_id" rows="4" inputmode="numeric" placeholder="123456789012345678&#10;987654321098765432">{{ $announcementChannels }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">Pega uno por linea. Tambien acepta IDs separados por coma o espacio.</p>
                    @error('discord_announcement_channel_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_selected_channel_id">Canales para seleccionados</label>
                    <textarea class="lumoryx-input mt-2 min-h-32" id="discord_selected_channel_id" name="discord_selected_channel_id" rows="4" inputmode="numeric" placeholder="Opcional. Si lo dejas vacio usa los canales de apertura.">{{ $selectedChannels }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">Ideal para anunciar aceptados en un canal publico y otro interno.</p>
                    @error('discord_selected_channel_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_announcement_role_id">Rol a mencionar en apertura/cierre</label>
                    <input class="lumoryx-input mt-2" id="discord_announcement_role_id" name="discord_announcement_role_id" inputmode="numeric" value="{{ old('discord_announcement_role_id', $settings['discord_announcement_role_id']) }}" placeholder="ID del rol opcional">
                    @error('discord_announcement_role_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_selected_role_id">Rol a mencionar en seleccionados</label>
                    <input class="lumoryx-input mt-2" id="discord_selected_role_id" name="discord_selected_role_id" inputmode="numeric" value="{{ old('discord_selected_role_id', $settings['discord_selected_role_id']) }}" placeholder="ID del rol opcional">
                    @error('discord_selected_role_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- 4. Logs del sistema --}}
        <section class="lumoryx-section-card">
            <div class="lumoryx-section-head">
                <span class="lumoryx-section-step">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h4"/></svg>
                </span>
                <div class="min-w-0 flex-grow-1">
                    <h2>Logs del sistema</h2>
                    <p>Envia registros privados a Discord cuando pase algo importante dentro del sistema de postulaciones.</p>
                </div>
                <label class="d-flex flex-shrink-0 align-items-center gap-2.5 text-sm font-black text-white">
                    <span class="d-none d-sm-inline">Activar</span>
                    <input class="lumoryx-toggle-checkbox" type="checkbox" name="discord_system_logs_enabled" value="1" @checked(old('discord_system_logs_enabled', $settings['discord_system_logs_enabled']))>
                </label>
            </div>

            <div class="lumoryx-section-body d-grid gap-4">
                <div>
                    <label class="lumoryx-label" for="discord_system_log_channel_id">Canales privados de logs</label>
                    <textarea class="lumoryx-input mt-2 min-h-32" id="discord_system_log_channel_id" name="discord_system_log_channel_id" rows="3" inputmode="numeric" placeholder="123456789012345678&#10;987654321098765432">{{ $systemLogChannels }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">
                        Si lo dejas vacio, se usara <span class="font-semibold text-slate-300">DISCORD_SYSTEM_LOG_CHANNEL_ID</span> del .env.
                    </p>
                    @error('discord_system_log_channel_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <p class="lumoryx-label">Eventos que se enviaran</p>
                    <div class="mt-2 d-grid gap-2 grid-cols-sm-2 grid-cols-lg-3">
                        @foreach ($systemLogEvents as $eventKey => $eventLabel)
                            <label class="lumoryx-check-card">
                                <input type="checkbox" name="discord_system_log_events[]" value="{{ $eventKey }}" @checked($systemLogSelectedEvents->contains($eventKey))>
                                <span class="lumoryx-check-box" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="lumoryx-check-title">{{ $eventLabel }}</span>
                                    <span class="lumoryx-check-key">{{ $eventKey }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('discord_system_log_events')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- 5. Mensajes de Discord --}}
        <section class="lumoryx-section-card">
            <div class="lumoryx-section-head">
                <span class="lumoryx-section-step">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
                </span>
                <div class="min-w-0 flex-grow-1">
                    <h2>Mensajes de Discord</h2>
                    <p>Personaliza el texto principal de cada embed. Si un campo queda vacio, se usa el mensaje por defecto.</p>
                </div>
            </div>

            <div class="lumoryx-section-body d-grid gap-4 grid-cols-xl-3">
                <div>
                    <label class="lumoryx-label" for="discord_open_message">Mensaje de apertura</label>
                    <textarea class="lumoryx-input mt-2 min-h-36" id="discord_open_message" name="discord_open_message" rows="5" maxlength="1000" placeholder="Ejemplo: Ya abrimos postulaciones. Revisa los requisitos y envia tu solicitud.">{{ old('discord_open_message', $settings['discord_open_message']) }}</textarea>
                    @error('discord_open_message')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_closed_message">Mensaje de cierre</label>
                    <textarea class="lumoryx-input mt-2 min-h-36" id="discord_closed_message" name="discord_closed_message" rows="5" maxlength="1000" placeholder="Ejemplo: Las postulaciones quedan cerradas temporalmente.">{{ old('discord_closed_message', $settings['discord_closed_message']) }}</textarea>
                    @error('discord_closed_message')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_selected_message">Mensaje de seleccionados</label>
                    <textarea class="lumoryx-input mt-2 min-h-36" id="discord_selected_message" name="discord_selected_message" rows="5" maxlength="1000" placeholder="Ejemplo: Felicidades a las personas seleccionadas para unirse al equipo.">{{ old('discord_selected_message', $settings['discord_selected_message']) }}</textarea>
                    @error('discord_selected_message')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="lumoryx-save-bar">
            <p>Los IDs se guardan limpios y sin duplicados. Puedes volver a esta pantalla cuando quieras.</p>
            <button class="lumoryx-button-primary" type="submit">Guardar configuracion</button>
        </div>
    </form>
</x-layouts.admin>
