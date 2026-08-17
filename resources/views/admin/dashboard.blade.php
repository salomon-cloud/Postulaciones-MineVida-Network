@php
    $statusMeta = [
        'pending' => ['label' => 'Pendientes', 'color' => '#cbd5e1', 'tone' => 'slate'],
        'in_review' => ['label' => 'En revision', 'color' => '#facc15', 'tone' => 'amber'],
        'interview' => ['label' => 'Entrevistas', 'color' => '#38bdf8', 'tone' => 'sky'],
        'accepted' => ['label' => 'Aceptadas', 'color' => '#34d399', 'tone' => 'emerald'],
        'rejected' => ['label' => 'Rechazadas', 'color' => '#fb7185', 'tone' => 'rose'],
        'cancelled' => ['label' => 'Canceladas', 'color' => '#a1a1aa', 'tone' => 'slate'],
    ];

    $needsAttention = ($stats['pending'] ?? 0) + ($stats['in_review'] ?? 0);
    $maxDaily = max($activityChart->max('count'), 1);
    $maxWeekly = max($weeklyChart->max('count'), 1);
    $hasDaily = $activityChart->sum('count') > 0;
    $hasWeekly = $weeklyChart->sum('count') > 0;
@endphp

<x-layouts.admin :title="'Admin | '.config('app.name', 'MineVida Network')">
    <x-lumoryx.page-header kicker="Administracion" title="Dashboard" description="Vista rapida del movimiento de postulaciones y carga actual del equipo.">
        <x-lumoryx.button href="{{ route('admin.applications.index') }}">Revisar postulaciones</x-lumoryx.button>
    </x-lumoryx.page-header>

    {{-- Fila 1: foco de trabajo + distribucion por estado (clickeable) --}}
    <section class="mt-5 d-grid gap-4 grid-cols-custom-xl" style="--gtc: minmax(0,.3fr) minmax(0,.7fr);">
        <div class="lumoryx-focus-card {{ $needsAttention > 0 ? 'is-active' : '' }}">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <p class="lumoryx-focus-label">Requieren atencion</p>
                <span class="lumoryx-focus-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                </span>
            </div>
            <p class="lumoryx-focus-value">{{ $needsAttention }}</p>
            <p class="lumoryx-focus-hint">
                {{ $stats['pending'] ?? 0 }} sin abrir &middot; {{ $stats['in_review'] ?? 0 }} en revision
            </p>
            @if ($needsAttention > 0)
                <a class="lumoryx-focus-action" href="{{ route('admin.applications.index', ['status' => 'pending']) }}">
                    Empezar a revisar
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            @else
                <p class="lumoryx-focus-clear">Bandeja al dia</p>
            @endif
        </div>

        <div class="lumoryx-panel p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-1 pb-3">
                <h2 class="text-sm font-black uppercase tracking-wide text-slate-400">Postulaciones por estado</h2>
                <span class="lumoryx-count-chip">{{ $stats['total'] }} total</span>
            </div>

            <div class="d-grid gap-2 grid-cols-sm-3 grid-cols-lg-6">
                @foreach ($statusMeta as $key => $meta)
                    @php
                        $count = $stats[$key] ?? 0;
                        $pct = $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0;
                    @endphp
                    <a class="lumoryx-status-tile {{ $count > 0 ? 'is-filled' : '' }}"
                       style="--tile: {{ $meta['color'] }};"
                       href="{{ route('admin.applications.index', ['status' => $key]) }}">
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
        </div>
    </section>

    {{-- Fila 2: metricas de ritmo --}}
    <section class="mt-4 d-grid gap-4 grid-cols-sm-2 grid-cols-xl-5">
        @foreach ([
            ['label' => 'Hoy', 'value' => $insights['today'], 'hint' => 'Nuevas solicitudes', 'tone' => 'amber', 'icon' => '<path d="M12 5v14M5 12h14"/>'],
            ['label' => 'Ultimos 7 dias', 'value' => $insights['week'], 'hint' => 'Actividad reciente', 'tone' => 'sky', 'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>'],
            ['label' => 'En proceso', 'value' => $insights['active'], 'hint' => 'Pendientes, revision y entrevista', 'tone' => 'violet', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
            ['label' => 'Aceptacion', 'value' => $insights['acceptance_rate'].'%', 'hint' => 'Sobre decisiones finales', 'tone' => 'emerald', 'icon' => '<path d="M20 6 9 17l-5-5"/>'],
            ['label' => 'Revision promedio', 'value' => $insights['avg_review_time'], 'hint' => 'Desde enviada hasta atendida', 'tone' => 'slate', 'icon' => '<path d="M13 2 3 14h8l-1 8 10-12h-8Z"/>'],
        ] as $kpi)
            <article class="lumoryx-kpi lumoryx-kpi-{{ $kpi['tone'] }}">
                <span class="lumoryx-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $kpi['icon'] !!}</svg>
                </span>
                <div class="min-w-0">
                    <p class="lumoryx-kpi-label">{{ $kpi['label'] }}</p>
                    <p class="lumoryx-kpi-value">{{ $kpi['value'] }}</p>
                    <p class="lumoryx-kpi-hint">{{ $kpi['hint'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    {{-- Fila 3: tendencia (con selector) + distribucion --}}
    <section style="--gtc: 1.05fr .95fr;" class="mt-4 d-grid gap-4 grid-cols-custom-xl align-items-start">
        <div class="lumoryx-panel p-5" x-data="{ period: 'daily' }">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="text-lg font-black text-white">Tendencia de postulaciones</h2>
                    <p class="mt-1 text-sm text-slate-400" x-text="period === 'daily' ? 'Recibidas dia por dia.' : 'Volumen agrupado por semana.'"></p>
                </div>
                <div class="lumoryx-segmented" role="group" aria-label="Periodo">
                    <button type="button" :class="period === 'daily' && 'is-active'" @click="period = 'daily'">14 dias</button>
                    <button type="button" :class="period === 'weekly' && 'is-active'" @click="period = 'weekly'">8 semanas</button>
                </div>
            </div>

            {{-- Diario --}}
            <div class="mt-5" x-show="period === 'daily'">
                @if ($hasDaily)
                    <div class="lumoryx-chart">
                        <div class="lumoryx-chart-axis">
                            <span>{{ $maxDaily }}</span>
                            <span>{{ (int) round($maxDaily / 2) }}</span>
                            <span>0</span>
                        </div>
                        <div class="lumoryx-chart-plot">
                            @foreach ($activityChart as $day)
                                <div class="lumoryx-chart-col" title="{{ $day['label'] }}: {{ $day['count'] }}">
                                    <div class="lumoryx-chart-bar-wrap">
                                        <span class="lumoryx-chart-bar" style="height: {{ $day['count'] > 0 ? max(4, round(($day['count'] / $maxDaily) * 100)) : 2 }}%;"></span>
                                    </div>
                                    <span class="lumoryx-chart-label">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <x-lumoryx.empty-state tone="amber" title="Sin actividad en 14 dias" body="No se han recibido postulaciones nuevas en las ultimas dos semanas.">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 14 3-3 3 3 4-5"/></svg>
                        </x-slot:icon>
                    </x-lumoryx.empty-state>
                @endif
            </div>

            {{-- Semanal --}}
            <div class="mt-5" x-show="period === 'weekly'" x-cloak>
                @if ($hasWeekly)
                    <div class="lumoryx-chart">
                        <div class="lumoryx-chart-axis">
                            <span>{{ $maxWeekly }}</span>
                            <span>{{ (int) round($maxWeekly / 2) }}</span>
                            <span>0</span>
                        </div>
                        <div class="lumoryx-chart-plot">
                            @foreach ($weeklyChart as $week)
                                <div class="lumoryx-chart-col" title="Semana del {{ $week['label'] }}: {{ $week['count'] }}">
                                    <div class="lumoryx-chart-bar-wrap">
                                        <span class="lumoryx-chart-bar is-emerald" style="height: {{ $week['count'] > 0 ? max(4, round(($week['count'] / $maxWeekly) * 100)) : 2 }}%;"></span>
                                    </div>
                                    <span class="lumoryx-chart-label">{{ $week['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <x-lumoryx.empty-state tone="emerald" title="Sin actividad en 8 semanas" body="No hay postulaciones registradas en este periodo.">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 14 3-3 3 3 4-5"/></svg>
                        </x-slot:icon>
                    </x-lumoryx.empty-state>
                @endif
            </div>
        </div>

        <div class="lumoryx-panel p-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="text-lg font-black text-white">Distribucion</h2>
                    <p class="mt-1 text-sm text-slate-400">Como se reparten todas las postulaciones.</p>
                </div>
            </div>

            <div style="--gtc: auto 1fr;" class="mt-5 d-grid gap-5 grid-cols-custom-md align-items-md-center">
                @php
                    $start = 0;
                    $donutParts = [];
                    foreach ($statusChart as $item) {
                        if ($item['percent'] > 0) {
                            $donutParts[] = $item['color'].' '.$start.'% '.($start + $item['percent']).'%';
                            $start += $item['percent'];
                        }
                    }
                    $donut = $donutParts ? implode(', ', $donutParts) : '#27272a 0% 100%';
                @endphp
                <div class="lumoryx-donut" style="background: conic-gradient({{ $donut }});">
                    <div class="lumoryx-donut-hole">
                        <p class="lumoryx-donut-value">{{ $stats['total'] }}</p>
                        <p class="lumoryx-donut-label">Total</p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    @foreach ($statusChart as $item)
                        <div class="lumoryx-legend-row {{ $item['count'] === 0 ? 'is-empty' : '' }}">
                            <span class="lumoryx-status-dot" style="--dot: {{ $item['color'] }};"></span>
                            <span class="lumoryx-legend-label">{{ $item['label'] }}</span>
                            <span class="lumoryx-legend-count">{{ $item['count'] }}</span>
                            <span class="lumoryx-legend-pct">{{ round($item['percent']) }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Fila 4: tipos + revisores --}}
    <section style="--gtc: .58fr .42fr;" class="mt-4 d-grid gap-4 grid-cols-custom-xl align-items-start">
        <div class="lumoryx-panel p-5">
            <h2 class="text-lg font-black text-white">Tipos mas solicitados</h2>
            <p class="mt-1 text-sm text-slate-400">Ayuda a detectar donde se concentra el interes.</p>

            <div class="mt-5 d-grid gap-4">
                @forelse ($typeChart as $type)
                    <div>
                        <div class="mb-1.5 d-flex align-items-center justify-content-between gap-3">
                            <span class="truncate text-sm font-bold text-slate-100">{{ $type['label'] }}</span>
                            <span class="d-flex align-items-center gap-2">
                                @if ($type['acceptance_rate'] !== null)
                                    <span class="lumoryx-pill lumoryx-pill-emerald">{{ $type['acceptance_rate'] }}% aceptacion</span>
                                @endif
                                <span class="text-sm font-black text-white">{{ $type['count'] }}</span>
                            </span>
                        </div>
                        <div class="lumoryx-track">
                            <span class="lumoryx-track-fill" style="width: {{ $type['percent'] }}%;"></span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $type['accepted'] }} aceptadas de {{ $type['count'] }}</p>
                    </div>
                @empty
                    <x-lumoryx.empty-state title="Sin datos" body="Cuando lleguen postulaciones apareceran aqui.">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        </x-slot:icon>
                    </x-lumoryx.empty-state>
                @endforelse
            </div>
        </div>

        <div class="lumoryx-panel p-5">
            <h2 class="text-lg font-black text-white">Admins que mas revisan</h2>
            <p class="mt-1 text-sm text-slate-400">Postulaciones atendidas por cada revisor.</p>

            <div class="mt-5 d-grid gap-2">
                @forelse ($topReviewers as $index => $reviewer)
                    @php $avatar = $reviewer->discordAvatarUrl(); @endphp
                    <div class="lumoryx-rank-row">
                        <span class="lumoryx-rank-pos {{ $index === 0 ? 'is-first' : '' }}">{{ $index + 1 }}</span>
                        <span class="lumoryx-user-avatar-shell h-9 w-9">
                            @if ($avatar)
                                <img class="lumoryx-user-avatar" src="{{ $avatar }}" alt="">
                            @else
                                <span class="lumoryx-user-avatar-fallback">{{ str($reviewer->name)->substr(0, 2)->upper() }}</span>
                            @endif
                        </span>
                        <div class="min-w-0 flex-grow-1">
                            <p class="truncate text-sm font-black text-white">{{ $reviewer->name }}</p>
                            <p class="text-xs text-slate-500">{{ $reviewer->role->label() }}</p>
                        </div>
                        <span class="lumoryx-count-chip">{{ $reviewer->reviewed_applications_count }}</span>
                    </div>
                @empty
                    <x-lumoryx.empty-state title="Sin revisiones" body="Cuando un admin atienda postulaciones aparecera aqui.">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/></svg>
                        </x-slot:icon>
                    </x-lumoryx.empty-state>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Fila 5: recientes --}}
    <section class="lumoryx-panel mt-4 overflow-hidden">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-b border-white/10 p-5">
            <div>
                <h2 class="text-lg font-black text-white">Postulaciones recientes</h2>
                <p class="mt-1 text-sm text-slate-400">Las ultimas que entraron al sistema.</p>
            </div>
            <x-lumoryx.button class="px-3 py-1.5" variant="secondary" href="{{ route('admin.applications.index') }}">Ver todas</x-lumoryx.button>
        </div>

        <div class="d-grid gap-2 p-4">
            @forelse ($latest as $application)
                @php
                    $accent = match ($application->status) {
                        \App\Enums\ApplicationStatus::Pending => '#cbd5e1',
                        \App\Enums\ApplicationStatus::InReview => '#facc15',
                        \App\Enums\ApplicationStatus::Interview => '#38bdf8',
                        \App\Enums\ApplicationStatus::Accepted => '#34d399',
                        \App\Enums\ApplicationStatus::Rejected => '#fb7185',
                        \App\Enums\ApplicationStatus::Cancelled => '#a1a1aa',
                    };
                    $userAvatar = $application->user?->discordAvatarUrl();
                @endphp
                <a href="{{ route('admin.applications.show', $application) }}" class="lumoryx-row-card">
                    <span class="lumoryx-row-accent" style="background: {{ $accent }};"></span>

                    <div class="lumoryx-row-main">
                        <span class="lumoryx-user-avatar-shell h-10 w-10">
                            @if ($userAvatar)
                                <img class="lumoryx-user-avatar" src="{{ $userAvatar }}" alt="">
                            @else
                                <span class="lumoryx-user-avatar-fallback">{{ str($application->minecraft_nick)->substr(0, 2)->upper() }}</span>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <div class="lumoryx-row-title">
                                <h3>{{ $application->minecraft_nick }}</h3>
                                <span class="lumoryx-pill">{{ $application->typeLabel() }}</span>
                            </div>
                            <p class="lumoryx-row-sub">{{ $application->user?->discord_username ?? 'Sin usuario' }}</p>
                        </div>
                    </div>

                    <dl class="lumoryx-meta-list">
                        <div class="lumoryx-meta">
                            <dt>Enviada</dt>
                            <dd>{{ $application->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>

                    <x-status-badge :status="$application->status" />
                </a>
            @empty
                <x-lumoryx.empty-state title="Sin postulaciones" body="Cuando alguien envie una solicitud aparecera aqui.">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l4 4v14H7V3Z"/><path d="M14 3v5h5M10 12h5M10 16h5"/></svg>
                    </x-slot:icon>
                </x-lumoryx.empty-state>
            @endforelse
        </div>
    </section>
</x-layouts.admin>
