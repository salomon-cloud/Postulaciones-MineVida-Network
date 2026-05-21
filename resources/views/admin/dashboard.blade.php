<x-layouts.admin :title="'Admin | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Administracion</p>
            <h1 class="lumoryx-title">Dashboard</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">Vista rapida del movimiento de postulaciones y carga actual del equipo.</p>
        </div>
        <x-lumoryx.button href="{{ route('admin.applications.index') }}">Revisar postulaciones</x-lumoryx.button>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
        @foreach ([
            'total' => 'Total',
            'pending' => 'Pendientes',
            'in_review' => 'En revision',
            'interview' => 'Entrevistas',
            'accepted' => 'Aceptadas',
            'rejected' => 'Rechazadas',
        ] as $key => $label)
            <div class="lumoryx-stat-card">
                <p class="text-3xl font-black text-white">{{ $stats[$key] ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-400">{{ $label }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="lumoryx-stat-card">
            <p class="text-sm font-semibold text-slate-400">Hoy</p>
            <p class="mt-2 text-3xl font-black text-white">{{ $insights['today'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Nuevas solicitudes</p>
        </div>
        <div class="lumoryx-stat-card">
            <p class="text-sm font-semibold text-slate-400">Ultimos 7 dias</p>
            <p class="mt-2 text-3xl font-black text-amber-100">{{ $insights['week'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Actividad reciente</p>
        </div>
        <div class="lumoryx-stat-card">
            <p class="text-sm font-semibold text-slate-400">En proceso</p>
            <p class="mt-2 text-3xl font-black text-sky-100">{{ $insights['active'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Pendientes, revision y entrevista</p>
        </div>
        <div class="lumoryx-stat-card">
            <p class="text-sm font-semibold text-slate-400">Aceptacion</p>
            <p class="mt-2 text-3xl font-black text-emerald-200">{{ $insights['acceptance_rate'] }}%</p>
            <p class="mt-1 text-xs text-slate-500">Aceptadas sobre decisiones finales</p>
        </div>
        <div class="lumoryx-stat-card">
            <p class="text-sm font-semibold text-slate-400">Revision promedio</p>
            <p class="mt-2 text-3xl font-black text-white">{{ $insights['avg_review_time'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Desde enviada hasta atendida</p>
        </div>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[.95fr_1.05fr]">
        <div class="lumoryx-panel p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-white">Actividad reciente</h2>
                    <p class="mt-1 text-sm text-slate-400">Postulaciones recibidas en los ultimos 14 dias.</p>
                </div>
                <span class="rounded-full border border-amber-200/20 bg-amber-200/10 px-3 py-1 text-xs font-bold text-amber-100">14 dias</span>
            </div>

            <div class="mt-6 flex h-56 items-end gap-2 rounded-lg border border-white/10 bg-white/[.025] p-4">
                @foreach ($activityChart as $day)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <div class="flex h-36 w-full items-end">
                            <div class="w-full rounded-t-md border border-amber-200/20 bg-gradient-to-t from-amber-400/70 to-amber-100/90 shadow-[0_0_24px_rgba(250,204,21,.12)]" style="height: {{ $day['height'] }}%;"></div>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-500">{{ $day['label'] }}</span>
                        <span class="text-xs font-black text-white">{{ $day['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lumoryx-panel p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-white">Estados</h2>
                    <p class="mt-1 text-sm text-slate-400">Distribucion completa de postulaciones.</p>
                </div>
                <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1 text-xs font-bold text-slate-300">{{ $stats['total'] }} total</span>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-[auto_1fr] md:items-center">
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
                <div class="mx-auto grid h-44 w-44 place-items-center rounded-full border border-white/10 shadow-panel" style="background: conic-gradient({{ $donut }});">
                    <div class="grid h-28 w-28 place-items-center rounded-full border border-white/10 bg-graphite-950/95 text-center">
                        <div>
                            <p class="text-3xl font-black text-white">{{ $stats['total'] }}</p>
                            <p class="text-xs font-semibold text-slate-400">Postulaciones</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($statusChart as $item)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $item['color'] }}"></span>
                                    <span class="truncate text-sm font-semibold text-slate-200">{{ $item['label'] }}</span>
                                </div>
                                <span class="text-sm font-black text-white">{{ $item['count'] }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-white/[.06]">
                                <div class="h-full rounded-full" style="width: {{ $item['percent'] }}%; background: {{ $item['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[.62fr_.38fr]">
        <div class="lumoryx-panel p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-white">Postulaciones por semana</h2>
                    <p class="mt-1 text-sm text-slate-400">Vista de volumen para planear mejor al equipo.</p>
                </div>
                <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1 text-xs font-bold text-slate-300">8 semanas</span>
            </div>

            <div class="mt-6 flex h-52 items-end gap-3 rounded-lg border border-white/10 bg-white/[.025] p-4">
                @foreach ($weeklyChart as $week)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <div class="flex h-32 w-full items-end">
                            <div class="w-full rounded-t-md border border-emerald-200/20 bg-gradient-to-t from-emerald-400/65 to-amber-100/85 shadow-[0_0_24px_rgba(52,211,153,.1)]" style="height: {{ $week['height'] }}%;"></div>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-500">{{ $week['label'] }}</span>
                        <span class="text-xs font-black text-white">{{ $week['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lumoryx-panel overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-lg font-black text-white">Admins que mas revisan</h2>
                <p class="mt-1 text-sm text-slate-400">Cantidad de postulaciones atendidas por cada revisor.</p>
            </div>
            <div class="divide-y divide-white/10">
                @forelse ($topReviewers as $reviewer)
                    <div class="flex items-center justify-between gap-4 p-4">
                        <div class="min-w-0">
                            <p class="truncate font-black text-white">{{ $reviewer->name }}</p>
                            <p class="text-sm text-slate-500">{{ $reviewer->role->label() }}</p>
                        </div>
                        <span class="rounded-lg border border-amber-300/20 bg-amber-300/10 px-3 py-2 text-sm font-black text-amber-100">
                            {{ $reviewer->reviewed_applications_count }}
                        </span>
                    </div>
                @empty
                    <x-lumoryx.empty-state title="Sin revisiones" body="Cuando un admin atienda postulaciones aparecera aqui." class="m-5" />
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[.42fr_.58fr]">
        <div class="lumoryx-panel p-5">
            <h2 class="text-lg font-black text-white">Tipos mas solicitados</h2>
            <p class="mt-1 text-sm text-slate-400">Ayuda a detectar donde se concentra el interes.</p>

            <div class="mt-5 space-y-4">
                @forelse ($typeChart as $type)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-4">
                            <span class="truncate text-sm font-semibold text-slate-200">{{ $type['label'] }}</span>
                            <span class="text-sm font-black text-white">{{ $type['count'] }}</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-white/[.06]">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-300 to-amber-200" style="width: {{ $type['percent'] }}%;"></div>
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-4 text-xs text-slate-500">
                            <span>{{ $type['accepted'] }} aceptadas</span>
                            <span>
                                @if ($type['acceptance_rate'] !== null)
                                    {{ $type['acceptance_rate'] }}% aceptacion
                                @else
                                    Sin decisiones finales
                                @endif
                            </span>
                        </div>
                    </div>
                @empty
                    <x-lumoryx.empty-state title="Sin datos" body="Cuando lleguen postulaciones apareceran aqui." />
                @endforelse
            </div>
        </div>

        <div class="lumoryx-panel overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-lg font-bold text-white">Recientes</h2>
            </div>
            <div class="divide-y divide-white/10">
                @forelse ($latest as $application)
                    <a href="{{ route('admin.applications.show', $application) }}" class="flex min-w-0 flex-col gap-3 p-5 transition hover:bg-white/[.04] sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">{{ $application->typeLabel() }} - {{ $application->minecraft_nick }}</p>
                            <p class="truncate text-sm text-slate-400">{{ $application->user->discord_username }} - {{ $application->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <x-status-badge :status="$application->status" />
                    </a>
                @empty
                    <div class="p-5 text-sm text-slate-400">No hay postulaciones.</div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.admin>
