<x-layouts.user :title="'Postulacion | '.config('app.name', 'MineVida Network')">
    @php
        $latestInterview = $application->interviews->sortByDesc('scheduled_at')->first();
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">{{ $application->typeLabel() }}</p>
            <h1 class="lumoryx-title truncate">{{ $application->minecraft_nick }}</h1>
            <p class="mt-2 text-sm text-slate-400">Enviada {{ $application->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <x-status-badge :status="$application->status" />
    </div>

    <div class="mt-6">
        <x-application-progress :status="$application->status" />
    </div>

    <section class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="lumoryx-panel p-5">
            <p class="lumoryx-kicker">Estado actual</p>
            <h2 class="mt-2 text-2xl font-black text-white">{{ $application->status->label() }}</h2>
            <p class="mt-3 text-sm leading-6 text-slate-400">{{ $application->status->userSummary() }}</p>
        </div>

        <div class="lumoryx-panel p-5">
            <p class="lumoryx-kicker">Que sigue</p>
            <h2 class="mt-2 text-2xl font-black text-white">Siguiente paso</h2>
            <p class="mt-3 text-sm leading-6 text-slate-400">{{ $application->status->nextStep() }}</p>
            @if ($application->cooldown_until && $application->cooldown_until->isFuture())
                <p class="mt-4 rounded-lg border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm font-semibold text-amber-100">
                    Podras volver a postular {{ $application->cooldown_until->diffForHumans() }}.
                </p>
            @endif
        </div>
    </section>

    @if ($latestInterview)
        <section class="lumoryx-panel-glow mt-6 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="lumoryx-kicker">Entrevista {{ $latestInterview->statusLabel() }}</p>
                    <h2 class="mt-1 text-2xl font-black text-white">{{ $latestInterview->scheduled_at?->format('d/m/Y H:i') ?? 'Fecha por definir' }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        {{ $latestInterview->location ?: 'El equipo te indicara el lugar o canal por Discord.' }}
                    </p>
                </div>
                <span class="rounded-full border border-sky-300/25 bg-sky-300/10 px-3 py-1 text-xs font-black text-sky-100">
                    {{ $latestInterview->interviewer?->name ?? 'Entrevistador por asignar' }}
                </span>
            </div>
            @if ($latestInterview->notes)
                <p class="lumoryx-break mt-4 whitespace-pre-line rounded-lg border border-white/10 bg-white/[.035] p-4 text-sm leading-6 text-slate-300">{{ $latestInterview->notes }}</p>
            @endif
        </section>
    @endif

    @if ($application->admin_response)
        <div class="lumoryx-panel-glow mt-6 p-5">
            <h2 class="text-lg font-bold text-white">Respuesta del staff</h2>
            <p class="lumoryx-break mt-3 whitespace-pre-line text-sm leading-6 text-slate-300">{{ $application->admin_response }}</p>
        </div>
    @endif

    <section class="mt-6 grid gap-5 lg:grid-cols-[.8fr_1.2fr]">
        <div class="lumoryx-panel p-5">
            <h2 class="text-lg font-bold text-white">Datos</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Edad</dt><dd class="text-white">{{ $application->age }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Pais</dt><dd class="lumoryx-break text-right text-white">{{ $application->country }}</dd></div>
                @if ($application->timezone)
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Zona horaria</dt><dd class="lumoryx-break text-right text-white">{{ $application->timezone }}</dd></div>
                @endif
                @if ($application->available_schedule)
                    <div><dt class="text-slate-400">Disponibilidad</dt><dd class="lumoryx-break mt-1 whitespace-pre-line text-white">{{ $application->available_schedule }}</dd></div>
                @endif
            </dl>

            @if ($application->canBeCancelledByUser())
                <form
                    class="mt-5 border-t border-white/10 pt-5"
                    method="POST"
                    action="{{ route('applications.cancel', $application) }}"
                    data-confirm
                    data-confirm-title="Cancelar postulacion"
                    data-confirm-message="Tu postulacion quedara cancelada y el equipo dejara de revisarla."
                    data-confirm-confirm-text="Cancelar postulacion"
                    data-confirm-tone="danger"
                >
                    @csrf
                    <button class="lumoryx-button-danger w-full" type="submit">Cancelar postulacion</button>
                </form>
            @endif
        </div>

        <div class="space-y-5">
            <div class="lumoryx-panel p-5">
                <h2 class="text-lg font-bold text-white">Historial del proceso</h2>
                <p class="mt-1 text-sm text-slate-400">Aqui veras cambios de estado, entrevistas y mensajes enviados por Discord.</p>
                <x-application-activity-timeline class="mt-5" :items="$timelineItems" />
            </div>

            <div class="lumoryx-panel overflow-hidden">
                <div class="border-b border-white/10 p-5">
                    <h2 class="text-lg font-bold text-white">Respuestas</h2>
                </div>
                <div class="divide-y divide-white/10">
                    @foreach ($application->answers as $answer)
                        <article class="p-5">
                            <h3 class="lumoryx-break text-sm font-semibold text-amber-100">{{ $answer->question }}</h3>
                            <p class="lumoryx-break mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ $answer->answer }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-layouts.user>
