<x-layouts.public :title="'Error | '.config('app.name', 'MineVida Network')">
    <section class="grid min-h-[60vh] place-items-center">
        <div class="max-w-lg text-center">
            <p class="text-sm font-semibold text-amber-200">500</p>
            <h1 class="mt-2 text-3xl font-black text-white">Algo salio mal</h1>
            <p class="mt-3 text-slate-400">El equipo puede revisar los logs del servidor sin exponer detalles sensibles.</p>
            <a class="lumoryx-button-primary mt-6" href="{{ auth()->check() ? route('dashboard') : route('home') }}">Volver</a>
        </div>
    </section>
</x-layouts.public>
