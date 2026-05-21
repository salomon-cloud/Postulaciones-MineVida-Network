<x-layouts.admin :title="'Seleccionados | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Anuncio publico</p>
            <h1 class="lumoryx-title">Seleccionados</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">Publica en Discord las personas aceptadas que ya fueron seleccionadas para el equipo.</p>
        </div>
        <div class="lumoryx-panel px-4 py-3 text-sm">
            <p class="text-slate-400">{{ count($selectedChannels) === 1 ? 'Canal configurado' : 'Canales configurados' }}</p>
            <p class="mt-1 font-semibold text-white">{{ count($selectedChannels) ?: 'Sin' }} {{ count($selectedChannels) === 1 ? 'canal' : 'canales' }}</p>
            @if ($selectedChannels)
                <p class="lumoryx-break mt-2 max-w-xs text-xs text-slate-500">{{ implode(', ', $selectedChannels) }}</p>
            @endif
        </div>
    </div>

    @error('applications')
        <div class="mt-5 rounded-lg border border-rose-400/30 bg-rose-500/10 p-4 text-sm text-rose-100">{{ $message }}</div>
    @enderror

    <form class="lumoryx-panel mt-6 overflow-hidden" method="POST" action="{{ route('admin.selected.publish') }}">
        @csrf

        <div class="flex flex-col gap-4 border-b border-white/10 p-5 sm:flex-row sm:items-center sm:justify-between">
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
                                <span class="sr-only">Seleccionar</span>
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
            <div class="p-8 text-center">
                <div class="lumoryx-icon-tile mx-auto h-12 w-12 text-sm font-black text-amber-100">OK</div>
                <h2 class="mt-4 text-lg font-bold text-white">No hay seleccionados pendientes</h2>
                <p class="mt-2 text-sm text-slate-400">Cuando aceptes postulaciones, apareceran aqui para anunciarlas en Discord.</p>
            </div>
        @endif
    </form>

    <section class="lumoryx-panel mt-6 overflow-hidden">
        <div class="border-b border-white/10 p-5">
            <h2 class="text-lg font-bold text-white">Anunciados recientemente</h2>
        </div>

        @if ($announced->isNotEmpty())
            <div class="divide-y divide-white/10">
                @foreach ($announced as $application)
                    <article class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="lumoryx-break font-semibold text-white">{{ $application->minecraft_nick }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ $application->typeLabel() }} - {{ $application->user?->discord_username ?? 'Sin usuario' }}</p>
                        </div>
                        <p class="shrink-0 text-sm text-slate-500">{{ $application->selected_announced_at?->format('d/m/Y H:i') }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="p-5 text-sm text-slate-400">Aun no se han publicado seleccionados.</p>
        @endif
    </section>
</x-layouts.admin>
