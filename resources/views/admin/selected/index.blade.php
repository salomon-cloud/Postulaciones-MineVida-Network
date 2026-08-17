<x-layouts.admin :title="'Seleccionados | '.config('app.name', 'MineVida Network')">
    <x-lumoryx.page-header kicker="Anuncio publico" title="Seleccionados" description="Publica en Discord las personas aceptadas que ya fueron seleccionadas para el equipo." glow="emerald" glow2="amber">
        <div class="lumoryx-channel-badge {{ count($selectedChannels) ? 'is-ready' : 'is-missing' }}">
            <span class="lumoryx-channel-badge-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9h16M4 15h16M10 3 8 21M16 3l-2 18"/></svg>
            </span>
            <div class="min-w-0">
                <p class="lumoryx-channel-badge-title">
                    {{ count($selectedChannels) ?: 'Sin' }} {{ count($selectedChannels) === 1 ? 'canal configurado' : 'canales configurados' }}
                </p>
                <p class="lumoryx-channel-badge-sub">
                    {{ count($selectedChannels) ? 'Listo para publicar en Discord' : 'Configura un canal en Ajustes' }}
                </p>
            </div>
        </div>
    </x-lumoryx.page-header>

    @error('applications')
        <div class="mt-5 rounded-lg border border-rose-400/30 bg-rose-500/10 p-4 text-sm text-rose-100">{{ $message }}</div>
    @enderror

    <form class="lumoryx-panel mt-6 overflow-hidden" method="POST" action="{{ route('admin.selected.publish') }}">
        @csrf

        <div class="d-flex flex-column gap-4 border-b border-white/10 p-5 flex-sm-row align-items-sm-center justify-content-sm-between">
            <div>
                <h2 class="text-lg font-bold text-white">Pendientes de anunciar</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $pending->count() }} persona(s) aceptada(s) sin anuncio publico.</p>
            </div>
            <button class="lumoryx-button-primary" type="submit" @disabled($pending->isEmpty())>Publicar seleccionados</button>
        </div>

        @if ($pending->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="lumoryx-table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <span class="visually-hidden">Seleccionar</span>
                            </th>
                            <th>Usuario</th>
                            <th>Tipo</th>
                            <th>Aceptada</th>
                            <th>Discord</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pending as $application)
                            <tr>
                                <td>
                                    <input class="rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="applications[]" value="{{ $application->id }}" checked>
                                </td>
                                <td>
                                    <p class="font-semibold text-white">{{ $application->minecraft_nick }}</p>
                                    <p class="text-xs text-slate-500">#{{ $application->id }}</p>
                                </td>
                                <td class="text-slate-300">{{ $application->typeLabel() }}</td>
                                <td class="text-slate-300">{{ $application->reviewed_at?->format('d/m/Y H:i') ?? $application->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <p class="lumoryx-break text-slate-300">{{ $application->user?->discord_username ?? 'Sin usuario' }}</p>
                                    <p class="lumoryx-break text-xs text-slate-500">{{ $application->user?->discord_id }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4">
                <x-lumoryx.empty-state tone="emerald" title="Todo al dia" body="No hay seleccionados pendientes de anunciar. Cuando aceptes postulaciones, apareceran aqui para publicarlas en Discord.">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </x-slot:icon>
                </x-lumoryx.empty-state>
            </div>
        @endif
    </form>

    <section class="lumoryx-panel mt-6 overflow-hidden">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-b border-white/10 p-5">
            <div>
                <h2 class="text-lg font-bold text-white">Anunciados recientemente</h2>
                <p class="mt-1 text-sm text-slate-400">Publicaciones ya enviadas a Discord.</p>
            </div>
            <span class="lumoryx-count-chip">{{ $announced->count() }}</span>
        </div>

        @if ($announced->isNotEmpty())
            <div class="d-grid gap-2 p-4">
                @foreach ($announced as $application)
                    @php
                        $announcedUser = $application->user;
                        $announcedAvatar = $announcedUser?->discordAvatarUrl();
                    @endphp
                    <article class="lumoryx-row-card">
                        <span class="lumoryx-row-accent" style="background: #6ee7b7;"></span>

                        <div class="lumoryx-row-main">
                            <span class="lumoryx-user-avatar-shell h-10 w-10">
                                @if ($announcedAvatar)
                                    <img class="lumoryx-user-avatar" src="{{ $announcedAvatar }}" alt="">
                                @else
                                    <span class="lumoryx-user-avatar-fallback">{{ str($application->minecraft_nick)->substr(0, 2)->upper() }}</span>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <div class="lumoryx-row-title">
                                    <h3>{{ $application->minecraft_nick }}</h3>
                                    <span class="lumoryx-pill lumoryx-pill-emerald">{{ $application->typeLabel() }}</span>
                                </div>
                                <p class="lumoryx-row-sub">{{ $announcedUser?->discord_username ?? 'Sin usuario' }}</p>
                            </div>
                        </div>

                        <dl class="lumoryx-meta-list">
                            <div class="lumoryx-meta">
                                <dt>Anunciado</dt>
                                <dd>{{ $application->selected_announced_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>
        @else
            <div class="p-4">
                <x-lumoryx.empty-state title="Sin publicaciones" body="Aun no se han publicado seleccionados en Discord.">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    </x-slot:icon>
                </x-lumoryx.empty-state>
            </div>
        @endif
    </section>
</x-layouts.admin>
