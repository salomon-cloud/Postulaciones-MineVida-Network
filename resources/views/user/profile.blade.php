<x-layouts.user :title="'Perfil | '.config('app.name', 'MineVida Network')">
    @php
        $roleGlow = match ($user->role) {
            \App\Enums\UserRole::Owner => 'rgba(250, 204, 21, 0.16)',
            \App\Enums\UserRole::Admin => 'rgba(125, 211, 252, 0.16)',
            \App\Enums\UserRole::Reviewer => 'rgba(110, 231, 183, 0.16)',
            default => 'rgba(148, 163, 184, 0.12)',
        };
    @endphp

    <div class="d-flex flex-column gap-4 flex-lg-row align-items-lg-center justify-content-lg-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">Cuenta conectada</p>
            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Perfil</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Consulta tu informacion de Discord y el resumen de tus procesos.</p>
        </div>
        <x-lumoryx.user-dropdown />
    </div>

    <section style="--gtc: .38fr .62fr;" class="mt-8 d-grid gap-5 grid-cols-custom-xl">
        <x-lumoryx.card class="position-relative overflow-hidden p-6">
            <div class="pointer-events-none position-absolute inset-0" style="background: radial-gradient(circle at 100% 0%, {{ $roleGlow }}, transparent 55%);"></div>
            <div class="position-relative d-flex align-items-start gap-4">
                @if ($user->discordAvatarUrl())
                    <img class="h-16 w-16 rounded-lg border border-white/15 object-cover shadow-panel" src="{{ $user->discordAvatarUrl() }}" alt="{{ $user->name }}">
                @else
                    <span class="lumoryx-icon-tile h-16 w-16 text-xl font-black text-amber-100">{{ str($user->name)->substr(0, 1)->upper() }}</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-2xl font-black text-white">{{ $user->name }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ $user->discord_username ?: 'Usuario de Discord' }}</p>
                    <div class="mt-3 d-inline-flex rounded-full bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-100 ring-1 ring-amber-400/25">
                        {{ $user->role->label() }}
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3 border-t border-white/10 pt-5">
                <div class="d-flex align-items-center justify-content-between gap-4 rounded-lg border border-white/10 bg-white/[.035] px-4 py-3">
                    <span class="text-sm text-slate-400">Discord ID</span>
                    <span class="truncate text-sm font-semibold text-white">{{ $user->discord_id ?: 'No disponible' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-4 rounded-lg border border-white/10 bg-white/[.035] px-4 py-3">
                    <span class="text-sm text-slate-400">Ultimo acceso</span>
                    <span class="truncate text-sm font-semibold text-white">{{ $user->last_login_at?->diffForHumans() ?? 'No registrado' }}</span>
                </div>
            </div>
        </x-lumoryx.card>

        <div class="space-y-5">
            <div class="d-grid gap-3 grid-cols-sm-3">
                <div class="lumoryx-stat-card">
                    <p class="text-3xl font-black text-white">{{ $totalApplications }}</p>
                    <p class="text-sm text-slate-400">Postulaciones</p>
                </div>
                <div class="lumoryx-stat-card">
                    <p class="text-3xl font-black text-amber-100">{{ $activeApplications }}</p>
                    <p class="text-sm text-slate-400">Activas</p>
                </div>
                <div class="lumoryx-stat-card">
                    <p class="text-3xl font-black text-emerald-200">{{ $acceptedApplications }}</p>
                    <p class="text-sm text-slate-400">Aceptadas</p>
                </div>
            </div>

            <x-lumoryx.card class="overflow-hidden p-0">
                <div class="border-b border-white/10 p-5">
                    <h2 class="text-lg font-black text-white">Procesos recientes</h2>
                </div>
                <div class="divide-y divide-white/10">
                    @forelse ($applications as $application)
                        @php
                            $statusAccent = match ($application->status) {
                                \App\Enums\ApplicationStatus::Pending => 'border-l-slate-400',
                                \App\Enums\ApplicationStatus::InReview => 'border-l-amber-400',
                                \App\Enums\ApplicationStatus::Interview => 'border-l-sky-400',
                                \App\Enums\ApplicationStatus::Accepted => 'border-l-emerald-400',
                                \App\Enums\ApplicationStatus::Rejected => 'border-l-rose-400',
                                \App\Enums\ApplicationStatus::Cancelled => 'border-l-zinc-500',
                            };
                        @endphp
                        <a href="{{ route('applications.show', $application) }}" class="d-block border-l-4 {{ $statusAccent }} p-5 transition hover:bg-white/[.04]">
                            <div class="d-flex flex-column gap-3 flex-sm-row align-items-sm-center justify-content-sm-between">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white">{{ $application->typeLabel() }} - {{ $application->minecraft_nick }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $application->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                                <x-status-badge :status="$application->status" />
                            </div>
                        </a>
                    @empty
                        <x-lumoryx.empty-state title="Sin procesos" body="Todavia no has enviado postulaciones." class="m-5" />
                    @endforelse
                </div>
            </x-lumoryx.card>
        </div>
    </section>
</x-layouts.user>
