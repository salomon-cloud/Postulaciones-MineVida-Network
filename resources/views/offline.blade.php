<x-layouts.public :title="'Sin conexion | '.config('app.name', 'MineVida Network')">
    <section class="grid min-h-[60vh] place-items-center px-4 py-12">
        <div class="lumoryx-panel max-w-lg p-7 text-center sm:p-9">
            <div class="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-lg border border-amber-200/25 bg-amber-300/10 text-xl font-black text-amber-100 shadow-panel">
                !
            </div>
            <p class="text-sm font-semibold text-amber-200">OFFLINE</p>
            <h1 class="mt-2 text-3xl font-black text-white">No hay conexion</h1>
            <p class="mt-3 text-slate-400">
                No pudimos conectar con el sistema de postulaciones. Revisa tu internet y vuelve a intentarlo.
            </p>
            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <button class="lumoryx-button-primary" type="button" onclick="window.location.reload()">Reintentar</button>
                <a class="lumoryx-button-secondary" href="{{ route('home') }}">Volver al inicio</a>
            </div>
        </div>
    </section>
</x-layouts.public>
