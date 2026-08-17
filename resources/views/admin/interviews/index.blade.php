<x-layouts.admin :title="'Entrevistas | '.config('app.name', 'MineVida Network')">
    <x-lumoryx.page-header kicker="Proceso de seleccion" title="Entrevistas" description="Agenda, revisa y da seguimiento a las entrevistas programadas para postulantes." glow="sky" glow2="amber">
        <x-lumoryx.button href="{{ route('admin.applications.index', ['status' => 'interview']) }}">Ver postulaciones en entrevista</x-lumoryx.button>

        <x-slot:stats>
            @foreach ([
                ['label' => 'Programadas', 'value' => $stats['scheduled'], 'hint' => 'En agenda', 'tone' => 'sky'],
                ['label' => 'Hoy', 'value' => $stats['today'], 'hint' => 'Para el dia de hoy', 'tone' => 'amber'],
                ['label' => 'Proximos 7 dias', 'value' => $stats['week'], 'hint' => 'Semana en curso', 'tone' => 'sky'],
                ['label' => 'Sin entrevistador', 'value' => $stats['unassigned'], 'hint' => 'Falta asignar', 'tone' => $stats['unassigned'] > 0 ? 'rose' : 'slate'],
                ['label' => 'Completadas', 'value' => $stats['completed'], 'hint' => 'Historico', 'tone' => 'emerald'],
                ['label' => 'Canceladas', 'value' => $stats['cancelled'], 'hint' => 'Historico', 'tone' => 'slate'],
            ] as $card)
                <div class="lumoryx-header-stat lumoryx-header-stat-{{ $card['tone'] }}">
                    <p class="lumoryx-header-stat-label">{{ $card['label'] }}</p>
                    <p class="lumoryx-header-stat-value">{{ $card['value'] }}</p>
                    <p class="lumoryx-header-stat-hint">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </x-slot:stats>
    </x-lumoryx.page-header>

    <section style="--gtc: .62fr .38fr;" class="mt-6 d-grid gap-5 grid-cols-custom-xl align-items-start">
        <div class="lumoryx-panel overflow-hidden">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-b border-white/10 p-5">
                <div>
                    <h2 class="text-lg font-black text-white">Proximas entrevistas</h2>
                    <p class="mt-1 text-sm text-slate-400">Ordenadas por fecha mas cercana.</p>
                </div>
                <span class="lumoryx-count-chip">{{ $upcoming->count() }}</span>
            </div>

            <div class="d-grid gap-3 p-4">
                @forelse ($upcoming as $interview)
                    @php
                        $when = $interview->scheduled_at;
                        $isToday = $when && $when->isToday();
                        $isPast = $when && $when->isPast();
                        $accent = $isPast ? '#fda4af' : ($isToday ? '#fcd34d' : '#7dd3fc');
                    @endphp

                    <a href="{{ route('admin.applications.show', $interview->application) }}" class="lumoryx-row-card">
                        <span class="lumoryx-row-accent" style="background: {{ $accent }};"></span>

                        <span class="lumoryx-date-tile" style="--tile-color: {{ $accent }};">
                            @if ($when)
                                <span class="lumoryx-date-tile-day">{{ $when->format('d') }}</span>
                                <span class="lumoryx-date-tile-month">{{ strtoupper($when->locale('es')->isoFormat('MMM')) }}</span>
                            @else
                                <span class="lumoryx-date-tile-day">--</span>
                                <span class="lumoryx-date-tile-month">S/F</span>
                            @endif
                        </span>

                        <div class="lumoryx-row-main">
                            <div class="min-w-0">
                                <div class="lumoryx-row-title">
                                    <h3>{{ $interview->application?->minecraft_nick ?? 'Sin postulante' }}</h3>
                                    @if ($isPast)
                                        <span class="lumoryx-pill lumoryx-pill-rose">Atrasada</span>
                                    @elseif ($isToday)
                                        <span class="lumoryx-pill lumoryx-pill-amber">Hoy</span>
                                    @endif
                                </div>
                                <p class="lumoryx-row-sub">
                                    {{ $interview->application?->typeLabel() }}
                                    <span class="text-slate-600">&middot;</span>
                                    {{ $interview->application?->user?->discord_username ?? 'Sin usuario' }}
                                </p>
                            </div>
                        </div>

                        <dl class="lumoryx-meta-list">
                            <div class="lumoryx-meta">
                                <dt>Fecha y hora</dt>
                                <dd>{{ $when?->format('d/m/Y H:i') ?? 'Por definir' }}</dd>
                            </div>
                            <div class="lumoryx-meta">
                                <dt>Entrevistador</dt>
                                <dd class="{{ $interview->interviewer ? '' : 'text-rose-300' }}">{{ $interview->interviewer?->name ?? 'Sin asignar' }}</dd>
                            </div>
                            @if ($interview->location)
                                <div class="lumoryx-meta">
                                    <dt>Lugar</dt>
                                    <dd>{{ $interview->location }}</dd>
                                </div>
                            @endif
                        </dl>
                    </a>
                @empty
                    <x-lumoryx.empty-state tone="sky" title="Sin entrevistas pendientes" body="Cuando programes una entrevista desde el detalle de una postulacion, aparecera aqui.">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                        </x-slot:icon>
                        <x-lumoryx.button variant="secondary" href="{{ route('admin.applications.index') }}">Ir a postulaciones</x-lumoryx.button>
                    </x-lumoryx.empty-state>
                @endforelse
            </div>
        </div>

        <div class="lumoryx-panel overflow-hidden">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-b border-white/10 p-5">
                <div>
                    <h2 class="text-lg font-black text-white">Cerradas recientemente</h2>
                    <p class="mt-1 text-sm text-slate-400">Completadas y canceladas.</p>
                </div>
                <span class="lumoryx-count-chip">{{ $completed->count() }}</span>
            </div>

            <div class="d-grid gap-2 p-4">
                @forelse ($completed as $interview)
                    @php
                        $isDone = $interview->status === \App\Models\ApplicationInterview::STATUS_COMPLETED;
                    @endphp
                    <a href="{{ route('admin.applications.show', $interview->application) }}" class="lumoryx-row-card">
                        <span class="lumoryx-row-accent" style="background: {{ $isDone ? '#6ee7b7' : '#fda4af' }};"></span>

                        <div class="lumoryx-row-main">
                            <span class="lumoryx-status-dot" style="--dot: {{ $isDone ? '#6ee7b7' : '#fda4af' }};"></span>
                            <div class="min-w-0">
                                <div class="lumoryx-row-title">
                                    <h3>{{ $interview->application?->minecraft_nick ?? 'Sin postulante' }}</h3>
                                </div>
                                <p class="lumoryx-row-sub">
                                    {{ $interview->interviewer?->name ?? 'Sin entrevistador' }}
                                    <span class="text-slate-600">&middot;</span>
                                    {{ $interview->scheduled_at?->format('d/m/Y') ?? 'Sin fecha' }}
                                </p>
                            </div>
                        </div>

                        <span class="lumoryx-pill {{ $isDone ? 'lumoryx-pill-emerald' : 'lumoryx-pill-rose' }}">{{ $interview->statusLabel() }}</span>
                    </a>
                @empty
                    <x-lumoryx.empty-state title="Sin historial" body="Aun no hay entrevistas completadas o canceladas.">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v5l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                        </x-slot:icon>
                    </x-lumoryx.empty-state>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.admin>
