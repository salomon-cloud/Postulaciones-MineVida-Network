<x-layouts.user :title="'Notificaciones | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Centro de actividad</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Notificaciones</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Revisa los cambios importantes de tus postulaciones en un solo lugar.</p>
        </div>
        <x-lumoryx.user-dropdown />
    </div>

    <section class="mt-8">
        <x-lumoryx.card class="overflow-hidden p-0">
            <div class="flex flex-col gap-3 border-b border-white/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <span class="lumoryx-icon-tile h-11 w-11 text-sm font-black text-amber-100">!</span>
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-black text-white">Actividad reciente</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $notifications->total() }} eventos registrados</p>
                    </div>
                </div>
                <x-lumoryx.button class="shrink-0" variant="secondary" href="{{ route('applications.index') }}">Mis postulaciones</x-lumoryx.button>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($notifications as $notification)
                    <a href="{{ $notification['application'] ? route('applications.show', $notification['application']) : route('applications.index') }}" class="block p-5 transition hover:bg-white/[.04]">
                        <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 gap-4">
                                <span class="lumoryx-icon-tile h-11 w-11 shrink-0 text-sm font-black text-amber-100">
                                    {{ $notification['status'] ? str($notification['status']->label())->substr(0, 1)->upper() : 'N' }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black text-white">{{ $notification['title'] }}</p>
                                        @if ($notification['status'])
                                            <x-status-badge :status="$notification['status']" />
                                        @endif
                                    </div>
                                    <p class="lumoryx-break mt-2 text-sm leading-6 text-slate-400">{{ $notification['body'] }}</p>
                                    @if ($notification['application'])
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-amber-100/80">{{ $notification['application']->typeLabel() }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 text-sm text-slate-500">{{ $notification['time'] }}</span>
                        </div>
                    </a>
                @empty
                    <x-lumoryx.empty-state title="Sin notificaciones" body="Cuando tu postulacion cambie de estado, lo veras aqui." class="m-5" />
                @endforelse
            </div>
        </x-lumoryx.card>
    </section>

    <div class="mt-5">{{ $notifications->links() }}</div>
</x-layouts.user>
