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

    $roleAccent = function ($role) {
        return match ($role) {
            \App\Enums\UserRole::Owner => '#facc15',
            \App\Enums\UserRole::Admin => '#7dd3fc',
            \App\Enums\UserRole::Reviewer => '#6ee7b7',
            default => '#64748b',
        };
    };
@endphp

<x-layouts.admin :title="'Usuarios | '.config('app.name', 'MineVida Network')">
    <div class="space-y-6">
        <section class="lumoryx-panel overflow-hidden">
            <div class="position-relative p-5 sm:p-7">
                <div class="pointer-events-none position-absolute inset-0 opacity-80">
                    <div class="position-absolute -right-24 -top-24 h-64 w-64 rounded-full bg-amber-300/10 blur-3xl"></div>
                    <div class="position-absolute bottom-0 left-1/4 h-44 w-72 rounded-full bg-sky-300/5 blur-3xl"></div>
                </div>

                <div class="position-relative d-flex flex-column gap-4 flex-lg-row align-items-lg-end justify-content-lg-between">
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

            <div class="d-grid border-t border-white/10 grid-cols-sm-2 grid-cols-lg-3 grid-cols-xxl-6">
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

        <form style="--gtc: 1fr auto auto;" class="lumoryx-panel d-grid gap-4 p-4 p-sm-5 grid-cols-custom-lg" method="GET" action="{{ route('admin.users.index') }}">
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
            <div class="d-flex align-items-end">
                <button class="lumoryx-button-primary min-h-11 w-100 lg:w-auto" type="submit">Buscar</button>
            </div>
            <div class="d-flex align-items-end">
                <x-lumoryx.button class="min-h-11 w-100 lg:w-auto" variant="secondary" href="{{ route('admin.users.index') }}">Limpiar</x-lumoryx.button>
            </div>
        </form>

        <section class="space-y-3">
            <div class="d-flex flex-column gap-2 flex-sm-row align-items-sm-end justify-content-sm-between">
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

                <article class="lumoryx-row-card">
                    <span class="lumoryx-row-accent" style="background: {{ $roleAccent($user->role) }};"></span>

                    <div class="lumoryx-row-main">
                        <span class="lumoryx-user-avatar-shell h-12 w-12">
                            @if ($avatarUrl)
                                <img class="lumoryx-user-avatar" src="{{ $avatarUrl }}" alt="">
                            @else
                                <span class="lumoryx-user-avatar-fallback">{{ $initial }}</span>
                            @endif
                            <span class="lumoryx-user-presence"></span>
                        </span>

                        <div class="min-w-0">
                            <div class="lumoryx-row-title">
                                <h3>{{ $displayName }}</h3>
                                <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $roleBadgeClasses($user->role) }}">
                                    {{ $user->role->label() }}
                                </span>
                            </div>
                            <p class="lumoryx-row-sub">
                                {{ $user->discord_username && $user->discord_username !== $displayName ? $user->discord_username : 'Cuenta conectada con Discord' }}
                                <span class="text-slate-600">&middot;</span>
                                <span class="text-slate-500">ID {{ $user->discord_id ?: '-' }}</span>
                            </p>
                        </div>
                    </div>

                    <dl class="lumoryx-meta-list">
                        <div class="lumoryx-meta">
                            <dt>Ultimo login</dt>
                            <dd>{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Sin registro' }}</dd>
                        </div>
                        <div class="lumoryx-meta">
                            <dt>Postulaciones</dt>
                            <dd>{{ $user->applications_count }}</dd>
                        </div>
                        <div class="lumoryx-meta">
                            <dt>Revisadas</dt>
                            <dd>{{ $user->reviewed_applications_count }}</dd>
                        </div>
                    </dl>

                    <form
                        class="d-flex flex-shrink-0 align-items-center gap-2"
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

                        <label class="visually-hidden" for="role-{{ $user->id }}">Rol de {{ $displayName }}</label>
                        <select id="role-{{ $user->id }}" class="lumoryx-input min-h-11" name="role">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        <button class="lumoryx-button-secondary min-h-11 flex-shrink-0 px-4" type="submit">Guardar</button>
                    </form>
                </article>
            @empty
                <x-lumoryx.empty-state title="Sin usuarios" body="No hay usuarios que coincidan con la busqueda actual.">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    </x-slot:icon>
                </x-lumoryx.empty-state>
            @endforelse
        </section>

        <div>{{ $users->links() }}</div>
    </div>
</x-layouts.admin>
