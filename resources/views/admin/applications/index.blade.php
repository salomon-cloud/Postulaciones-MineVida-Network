<x-layouts.admin :title="'Postulaciones admin | '.config('app.name', 'MineVida Network')">
    @php
        $statusMeta = [
            'pending' => ['label' => 'Pendientes', 'color' => '#cbd5e1'],
            'in_review' => ['label' => 'En revision', 'color' => '#facc15'],
            'interview' => ['label' => 'Entrevistas', 'color' => '#38bdf8'],
            'accepted' => ['label' => 'Aceptadas', 'color' => '#34d399'],
            'rejected' => ['label' => 'Rechazadas', 'color' => '#fb7185'],
            'cancelled' => ['label' => 'Canceladas', 'color' => '#a1a1aa'],
        ];
        $activeStatus = $filters['status'] ?? null;
    @endphp

    <x-lumoryx.page-header kicker="Panel de administracion" title="Postulaciones" description="Revisa, filtra y gestiona las postulaciones enviadas al servidor." glow="amber" glow2="sky" />

    <div class="admin-applications-shell">
    <section class="lumoryx-panel admin-applications-panel mt-6 p-4 p-sm-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3">
            <h2 class="text-sm font-black uppercase tracking-wide text-slate-400">Postulaciones por estado</h2>
            <a class="lumoryx-count-chip" href="{{ route('admin.applications.index') }}" title="Ver todas">{{ $stats['total'] }} total</a>
        </div>

        <div class="d-grid gap-2 grid-cols-sm-3 grid-cols-lg-6">
            @foreach ($statusMeta as $key => $meta)
                @php
                    $count = $stats[$key] ?? 0;
                    $pct = $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0;
                @endphp
                <a class="lumoryx-status-tile {{ $count > 0 ? 'is-filled' : '' }} {{ $activeStatus === $key ? 'is-active' : '' }}"
                   style="--tile: {{ $meta['color'] }};"
                   href="{{ route('admin.applications.index', array_filter(array_merge($filters, ['status' => $activeStatus === $key ? null : $key]))) }}">
                    <span class="lumoryx-status-tile-top">
                        <span class="lumoryx-status-dot" style="--dot: {{ $meta['color'] }};"></span>
                        <span class="lumoryx-status-tile-pct">{{ $pct }}%</span>
                    </span>
                    <span class="lumoryx-status-tile-value">{{ $count }}</span>
                    <span class="lumoryx-status-tile-label">{{ $meta['label'] }}</span>
                    <span class="lumoryx-status-tile-bar"><span style="width: {{ $pct }}%; background: {{ $meta['color'] }};"></span></span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="lumoryx-panel admin-applications-panel admin-applications-filter-panel mt-5 p-4 p-sm-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-4">
            <h2 class="text-sm font-black uppercase tracking-wide text-slate-400">Filtrar postulaciones</h2>
            <span class="lumoryx-count-chip">{{ $applications->total() }} resultado{{ $applications->total() === 1 ? '' : 's' }}</span>
        </div>

        <form method="GET" action="{{ route('admin.applications.index') }}">
            <div class="d-flex flex-wrap gap-3">
                <div class="lumoryx-filter-field">
                    <x-lumoryx.select name="type" label="Tipo">
                        <option value="">Todos</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-lumoryx.select>
                </div>
                <div class="lumoryx-filter-field">
                    <x-lumoryx.select name="status" label="Estado">
                        <option value="">Todos</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </x-lumoryx.select>
                </div>
                <div class="lumoryx-filter-field">
                    <x-lumoryx.input name="from" label="Fecha desde" type="date" value="{{ $filters['from'] ?? '' }}" />
                </div>
                <div class="lumoryx-filter-field">
                    <x-lumoryx.input name="to" label="Fecha hasta" type="date" value="{{ $filters['to'] ?? '' }}" />
                </div>
                <div class="lumoryx-filter-field-wide">
                    <x-lumoryx.input name="user" label="Buscar por usuario" type="search" value="{{ $filters['user'] ?? '' }}" placeholder="Nick o Discord" />
                </div>
            </div>

            <div class="d-flex flex-column gap-3 mt-4 pt-4 border-top border-white/10 flex-sm-row justify-content-sm-end">
                <x-lumoryx.button variant="secondary" href="{{ route('admin.applications.index') }}">Limpiar</x-lumoryx.button>
                <x-lumoryx.button type="submit">Aplicar filtros</x-lumoryx.button>
            </div>
        </form>
    </section>

    <section class="mt-5 d-grid gap-2 admin-applications-list">
        @forelse ($applications as $application)
            @php
                $accent = $statusMeta[$application->status->value]['color'] ?? '#64748b';
            @endphp
            <article class="lumoryx-row-card admin-applications-row-card">
                <span class="lumoryx-row-accent" style="background: {{ $accent }};"></span>

                <div class="lumoryx-row-main">
                    <span class="lumoryx-user-avatar-shell h-12 w-12">
                        <span class="lumoryx-user-avatar-fallback">{{ str($application->minecraft_nick)->substr(0, 2)->upper() }}</span>
                    </span>

                    <div class="min-w-0">
                        <div class="lumoryx-row-title">
                            <h3>{{ $application->minecraft_nick }}</h3>
                            <span class="lumoryx-pill">{{ $application->typeLabel() }}</span>
                        </div>
                        <p class="lumoryx-row-sub">{{ $application->user->discord_username ?? $application->user->discord_id }}</p>
                    </div>
                </div>

                <dl class="lumoryx-meta-list">
                    <div class="lumoryx-meta">
                        <dt>Estado</dt>
                        <dd><x-status-badge :status="$application->status" /></dd>
                    </div>
                    <div class="lumoryx-meta">
                        <dt>Enviada</dt>
                        <dd>{{ $application->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                <div class="d-flex flex-shrink-0 align-items-center gap-2">
                    <x-lumoryx.button class="px-3 py-1.5" variant="secondary" :href="route('admin.applications.show', $application)">Ver</x-lumoryx.button>
                    @can('updateStatus', \App\Models\Application::class)
                        <x-lumoryx.action-menu label="Mas acciones para {{ $application->minecraft_nick }}">
                            <form method="POST" action="{{ route('admin.applications.status', $application) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="pending">
                                <button class="lumoryx-action-menu-item" type="submit">Marcar pendiente</button>
                            </form>
                            <form method="POST" action="{{ route('admin.applications.status', $application) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_review">
                                <button class="lumoryx-action-menu-item" type="submit">Pasar a revision</button>
                            </form>
                            <form method="POST" action="{{ route('admin.applications.status', $application) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="interview">
                                <button class="lumoryx-action-menu-item" type="submit">Pasar a entrevista</button>
                            </form>

                            <div class="lumoryx-action-menu-divider"></div>

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
                                <button class="lumoryx-action-menu-item is-success" type="submit">Aceptar</button>
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
                                <button class="lumoryx-action-menu-item is-danger" type="submit">Rechazar</button>
                            </form>

                            <div class="lumoryx-action-menu-divider"></div>

                            <form
                                method="POST"
                                action="{{ route('admin.applications.destroy', $application) }}"
                                data-confirm
                                data-confirm-title="Eliminar postulacion"
                                data-confirm-message="La postulacion de {{ $application->minecraft_nick }} se ocultara del panel y del usuario. Esta accion conserva el registro interno."
                                data-confirm-confirm-text="Eliminar"
                                data-confirm-tone="danger"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="lumoryx-action-menu-item is-danger" type="submit">Eliminar</button>
                            </form>
                        </x-lumoryx.action-menu>
                    @endcan
                </div>
            </article>
        @empty
            <x-lumoryx.empty-state title="Sin postulaciones" body="No hay resultados con los filtros actuales.">
                <x-slot:icon>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/></svg>
                </x-slot:icon>
            </x-lumoryx.empty-state>
        @endforelse
    </section>

    <div class="mt-5">{{ $applications->links() }}</div>
    </div>
</x-layouts.admin>
