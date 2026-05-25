<x-layouts.user :title="'Ajustes | '.config('app.name', 'MineVida Network')">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Preferencias</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Ajustes</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Gestiona tu acceso y revisa como se conecta tu cuenta con Discord.</p>
        </div>
        <x-lumoryx.user-dropdown />
    </div>

    <section class="mt-8 grid gap-5 xl:grid-cols-2">
        <x-lumoryx.card class="p-6">
            <div class="flex items-start gap-4">
                <span class="lumoryx-icon-tile h-12 w-12 text-sm font-black text-amber-100">DC</span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black text-white">Cuenta de Discord</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Tu sesion usa Discord para identificarte y mantener tus postulaciones asociadas a tu cuenta.</p>
                </div>
            </div>
            <div class="mt-6 space-y-3">
                <div class="flex items-center justify-between gap-4 rounded-lg border border-white/10 bg-white/[.035] px-4 py-3">
                    <span class="text-sm text-slate-400">Usuario</span>
                    <span class="truncate text-sm font-semibold text-white">{{ $user->discord_username ?: $user->name }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-lg border border-white/10 bg-white/[.035] px-4 py-3">
                    <span class="text-sm text-slate-400">Discord ID</span>
                    <span class="truncate text-sm font-semibold text-white">{{ $user->discord_id ?: 'No disponible' }}</span>
                </div>
            </div>
        </x-lumoryx.card>

        <x-lumoryx.card class="p-6">
            <div class="flex items-start gap-4">
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

        <x-lumoryx.card class="p-6 xl:col-span-2">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-white">Sesion</h2>
                    <p class="mt-2 text-sm text-slate-400">Puedes cerrar sesion y volver a entrar con Discord cuando lo necesites.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="lumoryx-button-secondary w-full sm:w-auto" type="submit">Cerrar sesion</button>
                </form>
            </div>
        </x-lumoryx.card>
    </section>
</x-layouts.user>
