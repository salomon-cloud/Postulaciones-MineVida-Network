<x-layouts.public :title="'Iniciar sesion | '.config('app.name', 'MineVida Network')" :compact="true">
    <section class="d-grid min-h-[calc(100svh-5.45rem)] place-items-center px-4 py-12">
        <div class="w-100 max-w-md">
            <div class="lumoryx-panel-glow position-relative overflow-hidden p-7 text-center sm:p-9">
                <div class="pointer-events-none position-absolute inset-0" style="background: radial-gradient(circle at 50% 0%, rgba(88, 101, 242, 0.16), transparent 60%);"></div>

                <div class="position-relative">
                    <div class="mx-auto mb-5 d-grid h-16 w-16 place-items-center rounded-2xl border" style="border-color: rgba(88, 101, 242, 0.35); background: linear-gradient(160deg, rgba(88, 101, 242, 0.35), rgba(88, 101, 242, 0.08));">
                        <img class="h-8 w-8" src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" style="filter: brightness(0) invert(1);">
                    </div>

                    <p class="lumoryx-kicker">Bienvenido de vuelta</p>
                    <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Inicia sesion para continuar</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Usamos tu cuenta de Discord para identificarte, dar seguimiento a tus postulaciones y que el equipo pueda contactarte.
                    </p>

                    <a class="lumoryx-landing-button lumoryx-landing-button-discord mt-7 w-100" href="{{ route('login.discord') }}">
                        <img src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" aria-hidden="true">
                        <span>Continuar con Discord</span>
                    </a>

                    <p class="mt-5 text-xs leading-5 text-slate-500">
                        Al continuar aceptas que verifiquemos tu identidad con Discord. No compartimos tu informacion con terceros.
                    </p>
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-slate-500">
                <a class="font-semibold text-amber-200 transition hover:text-white" href="{{ route('home') }}">Volver al inicio</a>
            </p>
        </div>
    </section>
</x-layouts.public>
