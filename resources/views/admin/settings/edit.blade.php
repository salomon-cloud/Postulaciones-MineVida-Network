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
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Owner</p>
            <h1 class="lumoryx-title">Configuracion</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">Ajusta el flujo del sistema, la conexion con Discord y los canales donde se publican anuncios automaticos.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-3 xl:w-[520px]">
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
    </div>

    <form class="mt-6 space-y-6" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PATCH')

        <section class="lumoryx-panel overflow-hidden">
            <div class="grid gap-0 lg:grid-cols-[300px_1fr]">
                <div class="border-b border-white/10 bg-white/[.025] p-5 lg:border-b-0 lg:border-r">
                    <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">01</div>
                    <h2 class="mt-4 text-2xl font-black text-white">Estado general</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Controla si el sistema acepta nuevas solicitudes y que requisitos basicos se aplican al usuario.</p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <label class="group flex flex-col gap-4 rounded-lg border border-white/10 bg-graphite-900/70 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <span class="min-w-0">
                            <span class="block text-lg font-black text-white">Postulaciones abiertas</span>
                            <span class="mt-1 block text-sm leading-6 text-slate-400">Cuando cambies este estado, el sistema puede publicar un aviso en Discord si los anuncios estan activos.</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-3 rounded-full border border-white/10 bg-black/30 px-4 py-2 text-sm font-black text-white">
                            <span class="h-2.5 w-2.5 rounded-full {{ old('applications_open', $settings['applications_open']) ? 'bg-emerald-300' : 'bg-rose-300' }}"></span>
                            {{ old('applications_open', $settings['applications_open']) ? 'Activas' : 'Pausadas' }}
                            <input class="ml-2 rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="applications_open" value="1" @checked(old('applications_open', $settings['applications_open']))>
                        </span>
                    </label>

                    <div class="grid gap-5 md:grid-cols-2">
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
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_1.15fr]">
            <div class="lumoryx-panel p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">02</div>
                    <div>
                        <h2 class="text-2xl font-black text-white">Acceso Discord</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Define si el usuario debe pertenecer al servidor antes de usar el sistema.</p>
                    </div>
                </div>

                <label class="mt-6 flex flex-col gap-4 rounded-lg border border-white/10 bg-white/[.035] p-5 sm:flex-row sm:items-center sm:justify-between">
                    <span class="min-w-0">
                        <span class="block font-bold text-white">Verificar pertenencia al servidor</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-400">Requiere scopes de Discord y el ID del servidor configurado en el entorno.</span>
                    </span>
                    <input class="rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="require_discord_guild" value="1" @checked(old('require_discord_guild', $settings['require_discord_guild']))>
                </label>

                <div class="mt-5 rounded-lg border border-amber-300/20 bg-amber-300/10 p-4 text-sm leading-6 text-amber-50">
                    Si esta activo, el login revisa que la cuenta de Discord este dentro del servidor. Si tambien usas <span class="font-bold">guilds.join</span>, el bot puede intentar unirlo automaticamente.
                </div>
            </div>

            <div class="lumoryx-panel p-5 sm:p-6 xl:col-span-2">
                <div class="grid gap-6 xl:grid-cols-[320px_1fr]">
                    <div>
                        <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">LOG</div>
                        <h2 class="mt-4 text-2xl font-black text-white">Logs del sistema</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Envia registros privados a Discord cuando pase algo importante dentro del sistema de postulaciones.
                        </p>

                        <label class="mt-5 flex items-center justify-between gap-4 rounded-lg border border-white/10 bg-white/[.035] p-4">
                            <span>
                                <span class="block font-bold text-white">Activar auditoria</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">Si lo apagas, no se enviaran logs a Discord.</span>
                            </span>
                            <input class="rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="discord_system_logs_enabled" value="1" @checked(old('discord_system_logs_enabled', $settings['discord_system_logs_enabled']))>
                        </label>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-[1fr_1.2fr]">
                        <div>
                            <label class="lumoryx-label" for="discord_system_log_channel_id">Canales privados de logs</label>
                            <textarea class="lumoryx-input mt-2 min-h-40" id="discord_system_log_channel_id" name="discord_system_log_channel_id" rows="6" inputmode="numeric" placeholder="123456789012345678&#10;987654321098765432">{{ $systemLogChannels }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">
                                Si lo dejas vacio, se usara <span class="font-semibold text-slate-300">DISCORD_SYSTEM_LOG_CHANNEL_ID</span> del .env.
                            </p>
                            @error('discord_system_log_channel_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <p class="lumoryx-label">Eventos que se enviaran</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach ($systemLogEvents as $eventKey => $eventLabel)
                                    <label class="flex items-start gap-3 rounded-lg border border-white/10 bg-white/[.025] p-3 text-sm text-slate-300">
                                        <input class="mt-1 rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="discord_system_log_events[]" value="{{ $eventKey }}" @checked($systemLogSelectedEvents->contains($eventKey))>
                                        <span>
                                            <span class="block font-bold text-white">{{ $eventLabel }}</span>
                                            <span class="text-xs text-slate-500">{{ $eventKey }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('discord_system_log_events')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="lumoryx-panel p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">03</div>
                        <div>
                            <h2 class="text-2xl font-black text-white">Anuncios automaticos</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-400">Publica apertura, cierre y seleccionados en uno o varios canales de Discord.</p>
                        </div>
                    </div>
                    <label class="flex shrink-0 items-center gap-3 rounded-full border border-white/10 bg-white/[.035] px-4 py-2 text-sm font-black text-white">
                        Activar
                        <input class="rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="discord_announce_applications_window" value="1" @checked(old('discord_announce_applications_window', $settings['discord_announce_applications_window']))>
                    </label>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <div>
                        <label class="lumoryx-label" for="discord_announcement_channel_id">Canales para apertura y cierre</label>
                        <textarea class="lumoryx-input mt-2 min-h-32" id="discord_announcement_channel_id" name="discord_announcement_channel_id" rows="5" inputmode="numeric" placeholder="123456789012345678&#10;987654321098765432">{{ $announcementChannels }}</textarea>
                        <p class="mt-2 text-xs text-slate-500">Pega uno por linea. Tambien acepta IDs separados por coma o espacio.</p>
                        @error('discord_announcement_channel_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="lumoryx-label" for="discord_selected_channel_id">Canales para seleccionados</label>
                        <textarea class="lumoryx-input mt-2 min-h-32" id="discord_selected_channel_id" name="discord_selected_channel_id" rows="5" inputmode="numeric" placeholder="Opcional. Si lo dejas vacio usa los canales de apertura.">{{ $selectedChannels }}</textarea>
                        <p class="mt-2 text-xs text-slate-500">Ideal para anunciar aceptados en un canal publico y otro interno.</p>
                        @error('discord_selected_channel_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
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
            </div>
        </section>

        <section class="lumoryx-panel overflow-hidden">
            <div class="border-b border-white/10 bg-white/[.025] p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">04</div>
                    <div>
                        <h2 class="text-2xl font-black text-white">Mensajes de Discord</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Personaliza el texto principal de cada embed. Si un campo queda vacio, se usa el mensaje elegante por defecto.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-3">
                <div>
                    <label class="lumoryx-label" for="discord_open_message">Mensaje de apertura</label>
                    <textarea class="lumoryx-input mt-2 min-h-36" id="discord_open_message" name="discord_open_message" rows="6" maxlength="1000" placeholder="Ejemplo: Ya abrimos postulaciones. Revisa los requisitos y envia tu solicitud.">{{ old('discord_open_message', $settings['discord_open_message']) }}</textarea>
                    @error('discord_open_message')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_closed_message">Mensaje de cierre</label>
                    <textarea class="lumoryx-input mt-2 min-h-36" id="discord_closed_message" name="discord_closed_message" rows="6" maxlength="1000" placeholder="Ejemplo: Las postulaciones quedan cerradas temporalmente.">{{ old('discord_closed_message', $settings['discord_closed_message']) }}</textarea>
                    @error('discord_closed_message')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="lumoryx-label" for="discord_selected_message">Mensaje de seleccionados</label>
                    <textarea class="lumoryx-input mt-2 min-h-36" id="discord_selected_message" name="discord_selected_message" rows="6" maxlength="1000" placeholder="Ejemplo: Felicidades a las personas seleccionadas para unirse al equipo.">{{ old('discord_selected_message', $settings['discord_selected_message']) }}</textarea>
                    @error('discord_selected_message')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-white/10 bg-black/20 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <p class="text-sm leading-6 text-slate-400">Los IDs se guardan limpios y sin duplicados. Puedes volver a esta pantalla para agregar o quitar canales cuando quieras.</p>
                <button class="lumoryx-button-primary" type="submit">Guardar configuracion</button>
            </div>
        </section>
    </form>
</x-layouts.admin>
