@php
    $statCards = [
        ['label' => 'Categorias', 'value' => $stats['total'] ?? 0, 'hint' => 'Totales', 'tone' => 'border-white/10 bg-white/[.035]'],
        ['label' => 'Abiertas', 'value' => $stats['open'] ?? 0, 'hint' => 'Recibiendo solicitudes', 'tone' => 'border-emerald-300/20 bg-emerald-300/10'],
        ['label' => 'Cerradas', 'value' => $stats['closed'] ?? 0, 'hint' => 'Pausadas temporalmente', 'tone' => 'border-rose-300/20 bg-rose-300/10'],
        ['label' => 'Archivadas', 'value' => $stats['archived'] ?? 0, 'hint' => 'Guardadas para despues', 'tone' => 'border-slate-300/20 bg-slate-300/10'],
        ['label' => 'Preguntas', 'value' => $stats['questions'] ?? 0, 'hint' => 'En todos los formularios', 'tone' => 'border-amber-300/20 bg-amber-300/10'],
        ['label' => 'Postulaciones', 'value' => $stats['applications'] ?? 0, 'hint' => 'Registradas', 'tone' => 'border-sky-300/20 bg-sky-300/10'],
    ];
@endphp

<x-layouts.admin :title="'Categorias | '.config('app.name', 'MineVida Network')">
    <div class="space-y-6">
        <section class="lumoryx-panel overflow-hidden">
            <div class="relative p-5 sm:p-7">
                <div class="pointer-events-none absolute inset-0 opacity-80">
                    <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-amber-300/10 blur-3xl"></div>
                    <div class="absolute bottom-0 left-1/3 h-40 w-72 rounded-full bg-emerald-300/5 blur-3xl"></div>
                </div>

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="lumoryx-kicker">Panel de administracion</p>
                        <h1 class="lumoryx-title mt-2">Categorias</h1>
                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-400">
                            Gestiona las areas de postulacion, controla aperturas y entra al constructor para ordenar fases y preguntas.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a class="lumoryx-button-secondary" href="{{ route('applications.create') }}">Ver portal</a>
                        <x-lumoryx.button href="{{ route('admin.categories.create') }}">Nueva categoria</x-lumoryx.button>
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

        <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-white">Catalogo de formularios</h2>
                <p class="mt-1 text-sm text-slate-500">Cada tarjeta representa una categoria editable del sistema.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <span class="rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1.5 text-emerald-100">Abierta</span>
                <span class="rounded-full border border-rose-300/20 bg-rose-300/10 px-3 py-1.5 text-rose-100">Cerrada</span>
                <span class="rounded-full border border-slate-300/20 bg-slate-300/10 px-3 py-1.5 text-slate-300">Archivada</span>
            </div>
        </section>

        <section class="lumoryx-admin-category-grid">
            @forelse ($categories as $category)
                @php
                    $isArchived = $category->trashed();
                    $isClosed = ! $isArchived && ! $category->is_open;
                    $statusLabel = $isArchived ? 'Archivada' : ($category->is_open ? 'Abierta' : 'Cerrada');
                    $statusClasses = $isArchived
                        ? 'border-slate-300/20 bg-slate-300/10 text-slate-300'
                        : ($category->is_open
                            ? 'border-emerald-300/25 bg-emerald-300/10 text-emerald-100'
                            : 'border-rose-300/25 bg-rose-300/10 text-rose-100');
                    $accent = $category->accent_color ?: '#facc15';
                    $stepsCount = count($category->steps ?: []);
                @endphp

                <article class="lumoryx-admin-category-card group">
                    <div class="absolute inset-x-0 top-0 h-1" style="background: linear-gradient(90deg, {{ $accent }}, rgba(255,255,255,.08));"></div>

                    <div class="lumoryx-category-media">
                        @if ($category->imageUrl())
                            <img src="{{ $category->imageUrl() }}" alt="">
                        @else
                            <div class="lumoryx-category-media-empty">
                                <span class="lumoryx-category-media-badge">{{ $category->icon ?: str($category->name)->substr(0, 2)->upper() }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="relative flex flex-1 flex-col p-5 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-md border border-white/10 bg-graphite-950/70 text-sm font-black text-amber-100 shadow-inner">
                                    {{ $category->icon ?: str($category->name)->substr(0, 2)->upper() }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="lumoryx-break text-2xl font-black leading-tight text-white">{{ $category->name }}</h3>
                                        <span class="rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses }}">{{ $statusLabel }}</span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">/{{ $category->slug }}</p>
                                </div>
                            </div>

                            @if (! $isArchived)
                                <a class="lumoryx-button-secondary shrink-0 px-3 py-2" href="{{ route('admin.categories.edit', $category) }}">Editar</a>
                            @endif
                        </div>

                        <p class="lumoryx-category-summary mt-5 text-sm leading-6 text-slate-400">{{ $category->summary }}</p>

                        @if ($isClosed)
                            <div class="mt-5 rounded-lg border border-rose-300/20 bg-rose-300/10 p-4 text-sm leading-6 text-rose-100">
                                <p class="font-black text-white">Cerrada temporalmente</p>
                                <p class="mt-1">{{ $category->closed_message ?: 'Esta categoria esta cerrada temporalmente.' }}</p>
                                @if ($category->closed_until)
                                    <p class="mt-2 text-xs font-semibold text-rose-100/80">Reapertura estimada: {{ $category->closed_until->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        @elseif ($isArchived)
                            <div class="mt-5 rounded-lg border border-slate-300/20 bg-slate-300/10 p-4 text-sm leading-6 text-slate-300">
                                <p class="font-black text-white">Categoria archivada</p>
                                <p class="mt-1">No aparece para los usuarios. Puedes rehabilitarla cuando la necesites otra vez.</p>
                            </div>
                        @endif

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-white/10 bg-graphite-950/35 p-4">
                                <p class="text-2xl font-black text-white">{{ $category->questions_count }}</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Preguntas</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-graphite-950/35 p-4">
                                <p class="text-2xl font-black text-white">{{ $stepsCount }}</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Fases</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-graphite-950/35 p-4">
                                <p class="text-2xl font-black text-white">{{ $category->applications_count }}</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Postulaciones</p>
                            </div>
                        </div>

                        <div class="mt-auto flex flex-col gap-3 border-t border-white/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-xs leading-5 text-slate-500">
                                @if ($category->applications_count)
                                    Conserva historial de postulaciones.
                                @else
                                    Sin postulaciones registradas.
                                @endif
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                @if ($category->applications_count)
                                    <a
                                        class="lumoryx-button-secondary w-full px-3 py-2"
                                        href="{{ route('admin.applications.index', ['type' => $category->slug]) }}"
                                    >
                                        Ver postulaciones
                                    </a>
                                @endif

                                @if ($isArchived)
                                    <form method="POST" action="{{ route('admin.categories.restore', $category->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="lumoryx-button-success w-full px-3 py-2" type="submit">Rehabilitar</button>
                                    </form>
                                @else
                                    @if ($category->is_open)
                                        <form method="POST" action="{{ route('admin.categories.availability', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_open" value="0">
                                            <input type="hidden" name="closed_message" value="Esta categoria esta cerrada temporalmente.">
                                            <button class="lumoryx-button-danger w-full px-3 py-2" type="submit">Cerrar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.categories.availability', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_open" value="1">
                                            <button class="lumoryx-button-success w-full px-3 py-2" type="submit">Reabrir</button>
                                        </form>
                                    @endif

                                    @if (! $category->applications_count)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.categories.destroy', $category) }}"
                                            data-confirm
                                            data-confirm-title="Archivar categoria"
                                            data-confirm-message="La categoria {{ $category->name }} dejara de aparecer para los usuarios. Podras rehabilitarla despues."
                                            data-confirm-confirm-text="Archivar"
                                            data-confirm-tone="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button class="lumoryx-button-secondary w-full px-3 py-2" type="submit">Archivar</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="xl:col-span-2">
                    <x-lumoryx.empty-state title="Sin categorias" body="Crea la primera categoria para comenzar a recibir postulaciones." />
                </div>
            @endforelse
        </section>

        <div>{{ $categories->links() }}</div>
    </div>
</x-layouts.admin>
