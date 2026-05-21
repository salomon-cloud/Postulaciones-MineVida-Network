<x-layouts.admin :title="'Postulaciones admin | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Panel de administracion</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Postulaciones</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Revisa, filtra y gestiona las postulaciones enviadas al servidor.</p>
        </div>
        <p class="text-sm text-slate-500">Admin / Postulaciones</p>
    </div>

    <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-lumoryx.stat-card label="Pendientes" :value="$stats['pending']" hint="Esperando revision" tone="purple" icon="P" />
        <x-lumoryx.stat-card label="Aceptadas" :value="$stats['accepted']" hint="Este mes" tone="green" icon="A" />
        <x-lumoryx.stat-card label="Rechazadas" :value="$stats['rejected']" hint="Este mes" tone="red" icon="R" />
        <x-lumoryx.stat-card label="Entrevistas" :value="$stats['interview']" hint="Programadas" tone="blue" icon="E" />
    </section>

    <form class="lumoryx-panel mt-6 grid gap-4 p-5 md:grid-cols-6" method="GET" action="{{ route('admin.applications.index') }}">
        <x-lumoryx.select name="type" label="Tipo">
            <option value="">Todos</option>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </x-lumoryx.select>
        <x-lumoryx.select name="status" label="Estado">
            <option value="">Todos</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </x-lumoryx.select>
        <x-lumoryx.input name="from" label="Fecha desde" type="date" value="{{ $filters['from'] ?? '' }}" />
        <x-lumoryx.input name="to" label="Fecha hasta" type="date" value="{{ $filters['to'] ?? '' }}" />
        <div class="md:col-span-2">
            <x-lumoryx.input name="user" label="Buscar por usuario" type="search" value="{{ $filters['user'] ?? '' }}" placeholder="Nick o Discord" />
        </div>
        <div class="flex flex-col gap-3 md:col-span-6 sm:flex-row sm:justify-end">
            <x-lumoryx.button variant="secondary" href="{{ route('admin.applications.index') }}">Limpiar</x-lumoryx.button>
            <x-lumoryx.button type="submit">Aplicar filtros</x-lumoryx.button>
        </div>
    </form>

    <x-lumoryx.card class="mt-6 overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="lumoryx-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md border border-white/10 bg-graphite-850 font-black text-amber-100">{{ str($application->minecraft_nick)->substr(0, 1)->upper() }}</span>
                                    <div class="min-w-0">
                                        <p class="max-w-48 truncate font-semibold text-white">{{ $application->minecraft_nick }}</p>
                                        <p class="max-w-48 truncate text-xs text-slate-500">{{ $application->user->discord_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="font-semibold text-white">{{ $application->typeLabel() }}</p>
                                <p class="text-xs text-slate-500">{{ $application->type }}</p>
                            </td>
                            <td><x-status-badge :status="$application->status" /></td>
                            <td class="text-slate-300">
                                <p>{{ $application->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $application->created_at->format('H:i') }}</p>
                            </td>
                            <td>
                                <div class="flex min-w-max flex-wrap gap-2">
                                    <x-lumoryx.button class="px-3 py-1.5" variant="secondary" :href="route('admin.applications.show', $application)">Ver</x-lumoryx.button>
                                    @can('updateStatus', \App\Models\Application::class)
                                        <form method="POST" action="{{ route('admin.applications.status', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="pending">
                                            <button class="lumoryx-button-secondary px-3 py-1.5" type="submit">Pendiente</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.applications.status', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_review">
                                            <button class="lumoryx-button-purple px-3 py-1.5" type="submit">Revision</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.applications.status', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="interview">
                                            <button class="lumoryx-button-blue px-3 py-1.5" type="submit">Entrevista</button>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.applications.status', $application) }}"
                                            data-confirm
                                            data-confirm-title="Aceptar postulacion"
                                            data-confirm-message="La postulacion de {{ $application->minecraft_nick }} quedara aceptada y se enviara la notificacion correspondiente."
                                            data-confirm-confirm-text="Aceptar"
                                            data-confirm-tone="success"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="accepted">
                                            <input type="hidden" name="confirmed" value="1">
                                            <button class="lumoryx-button-success px-3 py-1.5" type="submit">Aceptar</button>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.applications.status', $application) }}"
                                            data-confirm
                                            data-confirm-title="Rechazar postulacion"
                                            data-confirm-message="La postulacion de {{ $application->minecraft_nick }} quedara rechazada y se notificara al usuario."
                                            data-confirm-confirm-text="Rechazar"
                                            data-confirm-tone="danger"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="confirmed" value="1">
                                            <button class="lumoryx-button-danger px-3 py-1.5" type="submit">Rechazar</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-lumoryx.empty-state title="Sin postulaciones" body="No hay resultados con los filtros actuales." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/10 p-5">{{ $applications->links() }}</div>
    </x-lumoryx.card>
</x-layouts.admin>
