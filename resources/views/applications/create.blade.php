<x-layouts.user :title="'Elegir postulacion | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Postulaciones</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Elige un area</h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-slate-300">Selecciona el equipo al que deseas postularte. Cada area tiene sus propias preguntas y requisitos.</p>
        </div>
        <x-lumoryx.user-dropdown />
    </div>

    <section class="lumoryx-panel mt-8 p-5 sm:p-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($types as $item)
                <article class="lumoryx-choice-card flex h-full flex-col {{ ($item['is_open'] ?? true) ? '' : 'opacity-80' }}">
                    <div class="flex items-start gap-4">
                        <div class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">{{ $item['icon'] ?? str($item['label'])->substr(0, 2)->upper() }}</div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-white">{{ $item['label'] }}</h2>
                                @if (! ($item['is_open'] ?? true))
                                    <span class="rounded-full border border-rose-300/25 bg-rose-300/10 px-2.5 py-1 text-xs font-semibold text-rose-200">Cerrada</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-400">{{ $item['summary'] }}</p>
                        </div>
                    </div>

                    @if ($item['is_open'] ?? true)
                        <x-lumoryx.button class="mt-6 w-full" :href="route('applications.create.type', $item['type'])">Postularme</x-lumoryx.button>
                    @else
                        <div class="mt-6 rounded-lg border border-rose-300/20 bg-rose-300/10 p-4">
                            <p class="text-sm font-semibold text-rose-100">No disponible por ahora</p>
                            <p class="mt-1 text-xs leading-5 text-slate-300">
                                {{ $item['closed_message'] ?: 'Esta categoria fue cerrada temporalmente.' }}
                                @if ($item['closed_until'])
                                    Podras intentarlo despues de {{ $item['closed_until']->format('Y-m-d H:i') }}.
                                @else
                                    Espera el proximo anuncio de apertura.
                                @endif
                            </p>
                            <x-lumoryx.button class="mt-3 w-full" variant="secondary" :href="route('applications.create.type', $item['type'])">Ver informacion</x-lumoryx.button>
                        </div>
                    @endif
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-4">
                    <x-lumoryx.empty-state title="Postulaciones cerradas" body="No hay categorias abiertas por ahora. Vuelve a revisar mas tarde." />
                </div>
            @endforelse
        </div>

        <div class="mt-6 flex flex-col gap-4 rounded-lg border border-white/10 bg-white/[.035] p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <span class="lumoryx-icon-tile h-11 w-11 text-sm font-black text-amber-100">i</span>
                <div class="min-w-0">
                    <h3 class="font-bold text-white">Antes de postularte</h3>
                    <p class="mt-1 text-sm text-slate-400">Revisa las normas y requisitos generales para evitar rechazos innecesarios.</p>
                </div>
            </div>
            <x-lumoryx.button class="shrink-0" variant="secondary" href="{{ route('home') }}#requisitos">Ver informacion</x-lumoryx.button>
        </div>
    </section>
</x-layouts.user>
