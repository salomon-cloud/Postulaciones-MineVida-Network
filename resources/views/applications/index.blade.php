<x-layouts.user :title="'Mis postulaciones | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h1 class="text-3xl font-black text-white sm:text-4xl">Mis postulaciones</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Consulta el estado de tus procesos y las novedades mas recientes.</p>
        </div>
        <x-lumoryx.user-chip />
    </div>

    @php
        $featured = $applications->first();
        $featuredInterview = $featured
            ? $featured->interviews
                ->where('status', \App\Models\ApplicationInterview::STATUS_SCHEDULED)
                ->filter(fn ($interview) => $interview->scheduled_at && $interview->scheduled_at->isFuture())
                ->sortBy('scheduled_at')
                ->first()
            : null;
    @endphp

    <section class="mt-8">
        @if ($featured)
            <x-lumoryx.card class="overflow-hidden p-0">
                <div class="grid gap-0 lg:grid-cols-[.34fr_.66fr]">
                    <div class="border-b border-white/10 p-6 lg:border-b-0 lg:border-r">
                        <div class="lumoryx-icon-tile h-14 w-14 text-sm font-black text-amber-100">{{ str($featured->typeLabel())->substr(0, 2)->upper() }}</div>
                        <h2 class="mt-5 text-2xl font-black text-white">{{ $featured->typeLabel() }}</h2>
                        <p class="lumoryx-break mt-3 text-sm leading-6 text-slate-400">Postulacion para el equipo de {{ str($featured->typeLabel())->lower() }} de {{ config('app.name', 'MineVida Network') }}.</p>
                        <div class="mt-5"><x-status-badge :status="$featured->status" /></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-black text-white">Estado actual</h3>
                        <div class="mt-6"><x-lumoryx.timeline :status="$featured->status" /></div>
                        <div class="mt-6 grid gap-3 md:grid-cols-2">
                            <div class="rounded-lg border border-white/10 bg-white/[.035] p-5">
                                <div class="flex gap-4">
                                    <span class="lumoryx-icon-tile h-10 w-10 text-sm font-black text-amber-100">i</span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white">{{ $featured->status->label() }}</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $featured->status->userSummary() }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-white/[.035] p-5">
                                <p class="font-bold text-white">Que debes esperar</p>
                                <p class="mt-2 text-sm leading-6 text-slate-400">{{ $featured->status->nextStep() }}</p>
                            </div>
                        </div>
                        @if ($featuredInterview)
                            <a href="{{ route('applications.show', $featured) }}" class="mt-3 block rounded-lg border border-sky-300/25 bg-sky-300/10 p-4 text-sm text-sky-100 transition hover:bg-sky-300/15">
                                Entrevista programada para {{ $featuredInterview->scheduled_at->format('d/m/Y H:i') }}.
                                {{ $featuredInterview->location ?: 'Revisa Discord para las indicaciones.' }}
                            </a>
                        @elseif ($featured->cooldown_until && $featured->cooldown_until->isFuture())
                            <a href="{{ route('applications.show', $featured) }}" class="mt-3 block rounded-lg border border-amber-300/25 bg-amber-300/10 p-4 text-sm text-amber-100 transition hover:bg-amber-300/15">
                                Podras volver a postular {{ $featured->cooldown_until->diffForHumans() }}.
                            </a>
                        @endif
                    </div>
                </div>
            </x-lumoryx.card>
        @else
            <x-lumoryx.empty-state title="Aun no tienes postulaciones" body="Elige un area y envia tu primera postulacion." />
        @endif
    </section>

    <section class="mt-8">
        <x-lumoryx.card class="overflow-hidden p-0">
            <div class="flex flex-col gap-4 border-b border-white/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <span class="lumoryx-icon-tile h-11 w-11 text-sm font-black text-amber-100">!</span>
                    <h2 class="truncate text-lg font-black text-white">Notificaciones recientes</h2>
                </div>
                <x-lumoryx.button class="shrink-0" variant="secondary" href="{{ route('dashboard') }}">Ver resumen</x-lumoryx.button>
            </div>
            <div class="space-y-3 p-5">
                @forelse ($recentNotifications as $notification)
                    <x-lumoryx.notification-item :title="$notification['title']" :body="$notification['body']" :time="$notification['time']" />
                @empty
                    <x-lumoryx.empty-state title="Sin notificaciones" body="Cuando tengas novedades sobre tus postulaciones apareceran aqui." />
                @endforelse
            </div>
        </x-lumoryx.card>
    </section>

    <div class="mt-5">{{ $applications->links() }}</div>
</x-layouts.user>
