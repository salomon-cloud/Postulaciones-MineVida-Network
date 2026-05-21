<x-layouts.public :title="'No encontrado | '.config('app.name', 'MineVida Network')">
    <section class="grid min-h-[60vh] place-items-center">
        <div class="max-w-lg text-center">
            <p class="text-sm font-semibold text-amber-200">404</p>
            <h1 class="mt-2 text-3xl font-black text-white">No encontrado</h1>
            <p class="mt-3 text-slate-400">La pagina o recurso solicitado no existe.</p>
            <a class="lumoryx-button-primary mt-6" href="{{ auth()->check() ? route('dashboard') : route('home') }}">Volver</a>
        </div>
    </section>
</x-layouts.public>
