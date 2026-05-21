<x-layouts.public title="Demasiados intentos | {{ config('app.name', 'MineVida Network') }}">
    <section class="grid min-h-[60vh] place-items-center px-4">
        <div class="max-w-lg text-center">
            <p class="text-sm font-semibold text-amber-200">429</p>
            <h1 class="mt-2 text-3xl font-black text-white">Demasiados intentos</h1>
            <p class="mt-3 text-slate-400">Espera un momento antes de volver a enviar. Esto protege el sistema y evita envios duplicados.</p>
            <a class="lumoryx-button-primary mt-6" href="{{ auth()->check() ? route('applications.create') : route('home') }}">Volver a postulaciones</a>
        </div>
    </section>
</x-layouts.public>
