<x-layouts.user :title="'Ajustes | '.config('app.name', 'MineVida Network')">
    <div class="d-flex flex-column gap-4 flex-lg-row align-items-lg-center justify-content-lg-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Preferencias</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Ajustes</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Gestiona tu acceso y revisa como se conecta tu cuenta con Discord.</p>
        </div>
        <x-lumoryx.user-dropdown />
    </div>

    <section class="mt-8 d-grid gap-5 grid-cols-xl-2">
        <x-lumoryx.card class="p-6">
            <div class="d-flex align-items-start gap-4">
                <span class="d-grid h-12 w-12 flex-shrink-0 place-items-center rounded-lg border text-sm font-black text-white" style="border-color: rgba(88, 101, 242, 0.35); background: linear-gradient(160deg, rgba(88, 101, 242, 0.4), rgba(88, 101, 242, 0.1));">
                    <img class="h-6 w-6" src="{{ asset('images/discord-icon-svgrepo-com.svg') }}" alt="" style="filter: brightness(0) invert(1);">
                </span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black text-white">Cuenta de Discord</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Tu sesion usa Discord para identificarte y mantener tus postulaciones asociadas a tu cuenta.</p>
                </div>
            </div>
            <div class="mt-6 space-y-3">
                <div class="d-flex align-items-center justify-content-between gap-4 rounded-lg border border-white/10 bg-white/[.035] px-4 py-3">
                    <span class="text-sm text-slate-400">Usuario</span>
                    <span class="truncate text-sm font-semibold text-white">{{ $user->discord_username ?: $user->name }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-4 rounded-lg border border-white/10 bg-white/[.035] px-4 py-3">
                    <span class="text-sm text-slate-400">Discord ID</span>
                    <span class="truncate text-sm font-semibold text-white">{{ $user->discord_id ?: 'No disponible' }}</span>
                </div>
            </div>
        </x-lumoryx.card>

        <x-lumoryx.card class="p-6">
            <div class="d-flex align-items-start gap-4">
                <span class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">!</span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black text-white">Notificaciones por Discord</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">El sistema intentara avisarte por mensaje privado cuando el equipo actualice una postulacion.</p>
                </div>
            </div>
            <div class="mt-6 rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-semibold text-emerald-200">
                {{ $user->discord_id ? 'Discord conectado correctamente.' : 'Discord no esta conectado.' }}
            </div>
            <p class="mt-4 text-sm leading-6 text-slate-400">Si no recibes mensajes, revisa que tengas los mensajes privados habilitados para miembros del servidor.</p>
        </x-lumoryx.card>

        <x-lumoryx.card class="p-6 col-span-xl-2">
            <div class="d-flex flex-column gap-4 flex-sm-row align-items-sm-center justify-content-sm-between">
                <div class="d-flex align-items-start gap-4">
                    <span class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-slate-300">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5M21 12H9" /></svg>
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-white">Sesion</h2>
                        <p class="mt-2 text-sm text-slate-400">Puedes cerrar sesion y volver a entrar con Discord cuando lo necesites.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="lumoryx-button-secondary w-100 sm:w-auto" type="submit">Cerrar sesion</button>
                </form>
            </div>
        </x-lumoryx.card>
    </section>
</x-layouts.user>
