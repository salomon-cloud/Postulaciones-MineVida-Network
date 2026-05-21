@php
    $statCards = [
        ['label' => 'Usuarios', 'value' => $stats['total'] ?? 0, 'hint' => 'Registrados', 'tone' => 'border-white/10 bg-white/[.035]'],
        ['label' => 'Owners', 'value' => $stats['owners'] ?? 0, 'hint' => 'Acceso total', 'tone' => 'border-amber-300/20 bg-amber-300/10'],
        ['label' => 'Admins', 'value' => $stats['admins'] ?? 0, 'hint' => 'Gestion del sistema', 'tone' => 'border-sky-300/20 bg-sky-300/10'],
        ['label' => 'Revisores', 'value' => $stats['reviewers'] ?? 0, 'hint' => 'Revision de postulaciones', 'tone' => 'border-emerald-300/20 bg-emerald-300/10'],
        ['label' => 'Usuarios base', 'value' => $stats['users'] ?? 0, 'hint' => 'Sin permisos admin', 'tone' => 'border-slate-300/20 bg-slate-300/10'],
        ['label' => 'Activos', 'value' => $stats['recent'] ?? 0, 'hint' => 'Ultimos 7 dias', 'tone' => 'border-violet-300/20 bg-violet-300/10'],
    ];

    $roleBadgeClasses = function ($role) {
        return match ($role) {
            \App\Enums\UserRole::Owner => 'border-amber-300/25 bg-amber-300/10 text-amber-100',
            \App\Enums\UserRole::Admin => 'border-sky-300/25 bg-sky-300/10 text-sky-100',
            \App\Enums\UserRole::Reviewer => 'border-emerald-300/25 bg-emerald-300/10 text-emerald-100',
            default => 'border-slate-300/20 bg-slate-300/10 text-slate-300',
        };
    };
@endphp

<x-layouts.admin :title="'Usuarios | '.config('app.name', 'MineVida Network')">
    <div class="space-y-6">
        <section class="lumoryx-panel overflow-hidden">
            <div class="relative p-5 sm:p-7">
                <div class="pointer-events-none absolute inset-0 opacity-80">
                    <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-amber-300/10 blur-3xl"></div>
                    <div class="absolute bottom-0 left-1/4 h-44 w-72 rounded-full bg-sky-300/5 blur-3xl"></div>
                </div>

                <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="lumoryx-kicker">Owner</p>
                        <h1 class="lumoryx-title mt-2">Usuarios</h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-400">
                            Administra permisos, revisa actividad y controla quien puede ver o gestionar postulaciones.
                        </p>
                    </div>

                    <div class="rounded-lg border border-amber-300/15 bg-amber-300/10 px-4 py-3 text-sm text-amber-100">
                        <p class="font-black text-white">Gestion sensible</p>
                        <p class="mt-1 text-xs leading-5 text-amber-100/80">Solo owners pueden cambiar roles.</p>
                    </div>
                </div>
            </div>

            <div class="grid border-t border-white/10 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
                @foreach ($statCards as $card)
                    <div class="border-b border-r border-white/10 p-4 last:border-r-0 lg:border-b-0">
                        <div class="rounded-lg border {{ $card['tone'] }} p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-black text-white">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $card['hint'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <form class="lumoryx-panel grid gap-4 p-4 sm:p-5 lg:grid-cols-[1fr_auto_auto]" method="GET" action="{{ route('admin.users.index') }}">
            <div>
                <label class="lumoryx-label" for="q">Buscar usuario</label>
                <input
                    id="q"
                    class="lumoryx-input mt-2"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Nombre, Discord ID o usuario de Discord"
                >
            </div>
            <div class="flex items-end">
                <button class="lumoryx-button-primary min-h-11 w-full lg:w-auto" type="submit">Buscar</button>
            </div>
            <div class="flex items-end">
                <x-lumoryx.button class="min-h-11 w-full lg:w-auto" variant="secondary" href="{{ route('admin.users.index') }}">Limpiar</x-lumoryx.button>
            </div>
        </form>

        <section class="space-y-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-black text-white">Directorio de permisos</h2>
                    <p class="mt-1 text-sm text-slate-500">Los cambios de rol se aplican al guardar cada usuario.</p>
                </div>
                <p class="text-sm text-slate-500">{{ $users->total() }} resultados</p>
            </div>

            @forelse ($users as $user)
                @php
                    $displayName = $user->discord_global_name ?: $user->name;
                    $initial = str($displayName)->substr(0, 1)->upper();
                    $avatarUrl = $user->discordAvatarUrl();
                @endphp

                <article class="relative overflow-hidden rounded-lg border border-white/10 bg-white/[.035] shadow-panel transition hover:border-amber-300/20 hover:bg-white/[.055]">
                    <div class="absolute inset-y-0 left-0 w-1 bg-amber-300/60"></div>
                    <div class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,.9fr)_minmax(21rem,.7fr)] lg:items-center">
                        <div class="flex min-w-0 items-center gap-4">
                            <span class="lumoryx-user-avatar-shell h-14 w-14">
                                @if ($avatarUrl)
                                    <img class="lumoryx-user-avatar" src="{{ $avatarUrl }}" alt="">
                                @else
                                    <span class="lumoryx-user-avatar-fallback">{{ $initial }}</span>
                                @endif
                                <span class="lumoryx-user-presence"></span>
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="max-w-full truncate text-lg font-black text-white">{{ $displayName }}</h3>
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $roleBadgeClasses($user->role) }}">
                                        {{ $user->role->label() }}
                                    </span>
                                </div>
                                @if ($user->discord_username && $user->discord_username !== $displayName)
                                    <p class="mt-1 max-w-full truncate text-sm text-slate-400">{{ $user->discord_username }}</p>
                                @else
                                    <p class="mt-1 max-w-full truncate text-sm text-slate-400">Cuenta conectada con Discord</p>
                                @endif
                            </div>
                        </div>

                        <dl class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                            <div class="rounded-lg border border-white/10 bg-graphite-950/35 px-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Discord ID</dt>
                                <dd class="mt-1 truncate text-sm font-semibold text-slate-200">{{ $user->discord_id ?: '-' }}</dd>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-graphite-950/35 px-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ultimo login</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-200">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Sin registro' }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-white/10 bg-graphite-950/35 px-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Post.</dt>
                                    <dd class="mt-1 text-xl font-black text-white">{{ $user->applications_count }}</dd>
                                </div>
                                <div class="rounded-lg border border-white/10 bg-graphite-950/35 px-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rev.</dt>
                                    <dd class="mt-1 text-xl font-black text-white">{{ $user->reviewed_applications_count }}</dd>
                                </div>
                            </div>
                        </dl>

                        <form
                            class="rounded-lg border border-white/10 bg-graphite-950/35 p-4"
                            method="POST"
                            action="{{ route('admin.users.role', $user) }}"
                            data-confirm
                            data-confirm-title="Actualizar rol"
                            data-confirm-message="Vas a cambiar los permisos de {{ $displayName }}. Revisa bien el rol antes de guardar."
                            data-confirm-confirm-text="Guardar rol"
                            data-confirm-tone="warning"
                        >
                            @csrf
                            @method('PATCH')

                            <label class="lumoryx-label" for="role-{{ $user->id }}">Rol del usuario</label>
                            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                                <select id="role-{{ $user->id }}" class="lumoryx-input min-h-11 flex-1" name="role">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                                <button class="lumoryx-button-secondary min-h-11 shrink-0 px-4" type="submit">Guardar</button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <x-lumoryx.empty-state title="Sin usuarios" body="No hay usuarios que coincidan con la busqueda actual." />
            @endforelse
        </section>

        <div>{{ $users->links() }}</div>
    </div>
</x-layouts.admin>
