<x-layouts.user :title="'Dashboard | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Panel de usuario</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Hola, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Administra tus postulaciones y revisa el estado de tus procesos.</p>
        </div>
        <x-lumoryx.user-chip />
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($types as $type => $label)
            <a class="lumoryx-card block" href="{{ route('applications.create.type', $type) }}">
                <div class="flex items-center gap-4">
                    <span class="lumoryx-icon-tile h-11 w-11 font-black text-amber-100">{{ str($label)->substr(0, 1)->upper() }}</span>
                    <div>
                        <p class="font-black text-white">{{ $label }}</p>
                        <p class="mt-1 text-sm text-slate-400">Enviar postulacion</p>
                    </div>
                </div>
            </a>
        @endforeach
    </section>

    <section class="lumoryx-panel-glow mt-8 overflow-hidden p-0">
        <div class="grid gap-0 xl:grid-cols-[.95fr_1.05fr]">
            <div class="border-b border-white/10 p-5 sm:p-6 xl:border-b-0 xl:border-r">
                <p class="lumoryx-kicker">Guia de postulacion</p>
                <h2 class="mt-2 text-2xl font-black text-white">Tu proceso, paso a paso</h2>
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Aqui puedes ver que postulaciones siguen abiertas, si tienes una entrevista pendiente y cuando podras volver a postular si alguna fue rechazada.
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                        <p class="text-2xl font-black text-white">{{ $activeCount }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Procesos activos</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                        <p class="text-2xl font-black text-emerald-200">{{ $acceptedCount }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Aceptadas</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                        <p class="text-2xl font-black text-amber-100">{{ $cooldownApplication ? '1' : '0' }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Reintentos en espera</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 p-5 sm:p-6">
                <div class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                    <div class="flex items-start gap-3">
                        <span class="lumoryx-icon-tile h-10 w-10 text-xs font-black text-amber-100">ST</span>
                        <div class="min-w-0">
                            <p class="font-black text-white">Que significa cada estado</p>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Pendiente espera revision, en revision significa que el staff ya la esta leyendo, entrevista indica una llamada o charla, y aceptada/rechazada es la decision final.
                            </p>
                        </div>
                    </div>
                </div>

                @if ($nextInterview)
                    <a href="{{ route('applications.show', $nextInterview->application_id) }}" class="block rounded-lg border border-sky-300/25 bg-sky-300/10 p-4 transition hover:bg-sky-300/15">
                        <p class="text-xs font-black uppercase tracking-wide text-sky-100">Proxima entrevista</p>
                        <p class="mt-1 text-lg font-black text-white">{{ $nextInterview->scheduled_at->format('d/m/Y H:i') }}</p>
                        <p class="mt-1 text-sm text-sky-100/80">{{ $nextInterview->location ?: 'El equipo te indicara el canal por Discord.' }}</p>
                    </a>
                @else
                    <div class="rounded-lg border border-white/10 bg-white/[.025] p-4">
                        <p class="font-black text-white">Sin entrevista pendiente</p>
                        <p class="mt-1 text-sm leading-6 text-slate-400">Si el equipo necesita hablar contigo, aparecera aqui con fecha, hora e indicaciones.</p>
                    </div>
                @endif

                @if ($cooldownApplication)
                    <a href="{{ route('applications.show', $cooldownApplication) }}" class="block rounded-lg border border-amber-300/25 bg-amber-300/10 p-4 transition hover:bg-amber-300/15">
                        <p class="text-xs font-black uppercase tracking-wide text-amber-100">Reintento disponible</p>
                        <p class="mt-1 text-sm leading-6 text-amber-50">
                            Podras volver a postular para {{ $cooldownApplication->typeLabel() }} {{ $cooldownApplication->cooldown_until->diffForHumans() }}.
                        </p>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-5 xl:grid-cols-[.34fr_.66fr]">
        <x-lumoryx.card class="p-5">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-black text-white">Resumen</h2>
                <x-lumoryx.button class="px-3 py-1.5" variant="secondary" href="{{ route('applications.index') }}">Ver todo</x-lumoryx.button>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="lumoryx-stat-card">
                    <p class="text-3xl font-black text-white">{{ $applications->count() }}</p>
                    <p class="text-sm text-slate-400">Totales</p>
                </div>
                <div class="lumoryx-stat-card">
                    <p class="text-3xl font-black text-amber-100">{{ $activeCount }}</p>
                    <p class="text-sm text-slate-400">Activas</p>
                </div>
                <div class="lumoryx-stat-card">
                    <p class="text-3xl font-black text-emerald-200">{{ $acceptedCount }}</p>
                    <p class="text-sm text-slate-400">Aceptadas</p>
                </div>
                <div class="lumoryx-stat-card">
                    <p class="truncate text-lg font-black text-slate-100">{{ auth()->user()->role->label() }}</p>
                    <p class="text-sm text-slate-400">Rol</p>
                </div>
            </div>
        </x-lumoryx.card>

        <x-lumoryx.card class="overflow-hidden p-0">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-lg font-black text-white">Mis postulaciones</h2>
            </div>
            <div class="divide-y divide-white/10">
                @forelse ($applications as $application)
                    @php
                        $scheduledInterview = $application->interviews
                            ->where('status', \App\Models\ApplicationInterview::STATUS_SCHEDULED)
                            ->filter(fn ($interview) => $interview->scheduled_at && $interview->scheduled_at->isFuture())
                            ->sortBy('scheduled_at')
                            ->first();
                    @endphp
                    <a href="{{ route('applications.show', $application) }}" class="block p-5 transition hover:bg-white/[.04]">
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-white">{{ $application->typeLabel() }} - {{ $application->minecraft_nick }}</p>
                                <p class="text-sm text-slate-400">{{ $application->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            <x-status-badge :status="$application->status" />
                        </div>
                        <p class="lumoryx-break mt-3 text-sm leading-6 text-slate-400">{{ $application->status->userSummary() }}</p>
                        @if ($scheduledInterview)
                            <div class="mt-3 rounded-lg border border-sky-300/20 bg-sky-300/10 px-4 py-3 text-sm text-sky-100">
                                Entrevista: {{ $scheduledInterview->scheduled_at->format('d/m/Y H:i') }}
                                @if ($scheduledInterview->location)
                                    <span class="text-sky-100/70">- {{ $scheduledInterview->location }}</span>
                                @endif
                            </div>
                        @elseif ($application->cooldown_until && $application->cooldown_until->isFuture())
                            <div class="mt-3 rounded-lg border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-100">
                                Reintento disponible {{ $application->cooldown_until->diffForHumans() }}.
                            </div>
                        @endif
                        <div class="mt-4">
                            <x-lumoryx.timeline :status="$application->status" />
                        </div>
                    </a>
                @empty
                    <x-lumoryx.empty-state title="Sin postulaciones" body="Cuando envies una postulacion aparecera aqui." class="m-5" />
                @endforelse
            </div>
        </x-lumoryx.card>
    </section>
</x-layouts.user>
