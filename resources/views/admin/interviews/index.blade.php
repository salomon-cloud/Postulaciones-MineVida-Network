<x-layouts.admin :title="'Entrevistas | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Proceso de seleccion</p>
            <h1 class="lumoryx-title">Entrevistas</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">Agenda, revisa y da seguimiento a las entrevistas programadas para postulantes.</p>
        </div>
        <x-lumoryx.button href="{{ route('admin.applications.index', ['status' => 'interview']) }}">Ver postulaciones en entrevista</x-lumoryx.button>
    </div>

    <section class="mt-6 grid gap-5 xl:grid-cols-[.62fr_.38fr]">
        <div class="lumoryx-panel overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-lg font-black text-white">Proximas entrevistas</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $upcoming->count() }} entrevista(s) pendiente(s).</p>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($upcoming as $interview)
                    <a href="{{ route('admin.applications.show', $interview->application) }}" class="block p-5 transition hover:bg-white/[.04]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-lg font-black text-white">{{ $interview->application?->minecraft_nick }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ $interview->application?->typeLabel() }} - {{ $interview->application?->user?->discord_username }}</p>
                                @if ($interview->location)
                                    <p class="lumoryx-break mt-2 text-xs text-slate-500">{{ $interview->location }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 rounded-lg border border-sky-300/20 bg-sky-300/10 px-4 py-3 text-sm text-sky-100">
                                <p class="font-black">{{ $interview->scheduled_at?->format('d/m/Y H:i') ?? 'Fecha por definir' }}</p>
                                <p class="mt-1 text-xs text-sky-100/75">{{ $interview->interviewer?->name ?? 'Sin entrevistador' }}</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <x-lumoryx.empty-state title="Sin entrevistas pendientes" body="Cuando programes entrevistas apareceran aqui." class="m-5" />
                @endforelse
            </div>
        </div>

        <div class="lumoryx-panel overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-lg font-black text-white">Cerradas recientemente</h2>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($completed as $interview)
                    <a href="{{ route('admin.applications.show', $interview->application) }}" class="block p-5 transition hover:bg-white/[.04]">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate font-black text-white">{{ $interview->application?->minecraft_nick }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ $interview->statusLabel() }} - {{ $interview->interviewer?->name ?? 'Sin entrevistador' }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs font-black {{ $interview->status === \App\Models\ApplicationInterview::STATUS_COMPLETED ? 'border-emerald-300/25 bg-emerald-300/10 text-emerald-100' : 'border-rose-300/25 bg-rose-300/10 text-rose-100' }}">
                                {{ $interview->statusLabel() }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="p-5 text-sm text-slate-400">Aun no hay entrevistas completadas.</p>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.admin>
