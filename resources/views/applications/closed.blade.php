<x-layouts.user :title="'Categoria cerrada | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Postulaciones</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">{{ $category->name }} esta cerrada</h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-slate-300">Esta categoria no esta recibiendo solicitudes nuevas por ahora.</p>
        </div>
        <x-lumoryx.user-chip />
    </div>

    <section class="mt-8 grid gap-5 lg:grid-cols-[.36fr_.64fr]">
        <x-lumoryx.card class="p-6">
            <div class="flex items-start gap-4">
                <span class="lumoryx-icon-tile h-14 w-14 text-sm font-black text-amber-100">{{ $category->icon ?: str($category->name)->substr(0, 2)->upper() }}</span>
                <div class="min-w-0">
                    <h2 class="text-2xl font-black text-white">{{ $category->name }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ $category->summary }}</p>
                </div>
            </div>
        </x-lumoryx.card>

        <x-lumoryx.card class="p-6">
            <div class="flex items-start gap-4">
                <span class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-rose-100">!</span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black text-white">Categoria cerrada temporalmente</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-300">
                        {{ $category->closed_message ?: 'El equipo cerro esta categoria temporalmente. Vuelve a revisar mas tarde para saber cuando abre de nuevo.' }}
                    </p>

                    @if ($category->closed_until)
                        <div class="mt-4 rounded-lg border border-amber-300/20 bg-amber-300/10 p-4">
                            <p class="text-sm font-semibold text-amber-100">Proxima apertura estimada</p>
                            <p class="mt-1 text-lg font-black text-white">{{ $category->closed_until->format('Y-m-d H:i') }}</p>
                        </div>
                    @else
                        <div class="mt-4 rounded-lg border border-white/10 bg-white/[.035] p-4 text-sm text-slate-400">
                            Todavia no hay una fecha de reapertura. Mantente atento a los anuncios del servidor.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <x-lumoryx.button variant="secondary" href="{{ route('applications.create') }}">Ver otras categorias</x-lumoryx.button>
                <x-lumoryx.button href="{{ route('dashboard') }}">Ir al panel</x-lumoryx.button>
            </div>
        </x-lumoryx.card>
    </section>
</x-layouts.user>
