<x-layouts.admin :title="'Detalle admin | '.config('app.name', 'MineVida Network')">
    @php
        $latestInterview = $application->interviews->sortByDesc('scheduled_at')->first();
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="lumoryx-kicker">{{ $application->typeLabel() }}</p>
            <h1 class="lumoryx-title truncate">{{ $application->minecraft_nick }}</h1>
            <p class="mt-2 truncate text-sm text-slate-400">{{ $application->user->discord_username }} - {{ $application->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <x-status-badge :status="$application->status" />
    </div>

    <div class="mt-6">
        <x-application-progress :status="$application->status" />
    </div>

    @if ($latestInterview)
        <section class="lumoryx-panel-glow mt-6 p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="lumoryx-kicker">Entrevista {{ $latestInterview->statusLabel() }}</p>
                    <h2 class="mt-1 text-2xl font-black text-white">{{ $latestInterview->scheduled_at?->format('d/m/Y H:i') ?? 'Fecha por definir' }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        Entrevistador: {{ $latestInterview->interviewer?->name ?? 'Sin asignar' }}
                        @if ($latestInterview->location)
                            <span class="text-slate-500">-</span> {{ $latestInterview->location }}
                        @endif
                    </p>
                </div>
                <span class="rounded-full border px-3 py-1 text-xs font-black {{ $latestInterview->status === \App\Models\ApplicationInterview::STATUS_COMPLETED ? 'border-emerald-300/25 bg-emerald-300/10 text-emerald-100' : ($latestInterview->status === \App\Models\ApplicationInterview::STATUS_CANCELLED ? 'border-rose-300/25 bg-rose-300/10 text-rose-100' : 'border-sky-300/25 bg-sky-300/10 text-sky-100') }}">
                    {{ $latestInterview->statusLabel() }}
                </span>
            </div>
        </section>
    @endif

    <section class="mt-6 grid gap-5 xl:grid-cols-[.36fr_.64fr]">
        <div class="space-y-5">
            <div class="lumoryx-panel p-5">
                <h2 class="text-lg font-bold text-white">Resumen</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Discord ID</dt><dd class="lumoryx-break text-right text-white">{{ $application->user->discord_id }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Edad</dt><dd class="text-white">{{ $application->age }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Pais</dt><dd class="lumoryx-break text-right text-white">{{ $application->country }}</dd></div>
                    @if ($application->timezone)
                        <div class="flex justify-between gap-4"><dt class="text-slate-400">Zona horaria</dt><dd class="lumoryx-break text-right text-white">{{ $application->timezone }}</dd></div>
                    @endif
                    @if ($application->available_schedule)
                        <div><dt class="text-slate-400">Disponibilidad</dt><dd class="lumoryx-break mt-1 whitespace-pre-line text-white">{{ $application->available_schedule }}</dd></div>
                    @endif
                </dl>
            </div>

            @can('updateStatus', \App\Models\Application::class)
                <form class="lumoryx-panel p-5" method="POST" action="{{ route('admin.applications.status', $application) }}">
                    @csrf
                    @method('PATCH')

                    <div>
                        <h2 class="text-lg font-bold text-white">Decision del equipo</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-400">Agrega una respuesta opcional y cambia el estado desde aqui.</p>
                    </div>

                    <label class="lumoryx-label mt-5 block" for="admin_response">Respuesta para el postulante</label>
                    <textarea class="lumoryx-input mt-2" id="admin_response" name="admin_response" rows="5" maxlength="2500" placeholder="Mensaje opcional para explicar la decision.">{{ old('admin_response', $application->admin_response) }}</textarea>
                    @error('admin_response')
                        <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                    @enderror

                    <label class="mt-4 flex items-start gap-3 rounded-lg border border-white/10 bg-white/[.03] p-3 text-sm text-slate-200">
                        <input class="mt-0.5 rounded border-white/10 bg-graphite-950 text-amber-300 focus:ring-amber-300" type="checkbox" name="confirmed" value="1" @checked(old('confirmed'))>
                        <span>Confirmo esta decision si estoy aceptando o rechazando la postulacion.</span>
                    </label>
                    @error('confirmed')
                        <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                    @enderror
                    @error('status')
                        <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                    @enderror

                    <div class="mt-5 grid gap-2 sm:grid-cols-2">
                        <button class="lumoryx-button-secondary justify-center px-3 py-2" type="submit" name="status" value="pending">Pendiente</button>
                        <button class="lumoryx-button-secondary justify-center px-3 py-2" type="submit" name="status" value="in_review">En revision</button>
                        <button class="lumoryx-button-secondary justify-center px-3 py-2" type="submit" name="status" value="interview">Entrevista</button>
                        <button class="lumoryx-button-secondary justify-center px-3 py-2" type="submit" name="status" value="cancelled">Cancelar</button>
                        <button class="lumoryx-button-success justify-center px-3 py-2" type="submit" name="status" value="accepted">Aceptar</button>
                        <button class="lumoryx-button-danger justify-center px-3 py-2" type="submit" name="status" value="rejected">Rechazar</button>
                    </div>
                </form>
            @endcan

            @can('updateStatus', \App\Models\Application::class)
                <div class="lumoryx-panel p-5">
                    <div class="flex items-start gap-4">
                        <span class="lumoryx-icon-tile h-11 w-11 text-sm font-black text-sky-100">IN</span>
                        <div>
                            <h2 class="text-lg font-bold text-white">Entrevista</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-400">Programa una fecha, asigna entrevistador y guarda indicaciones internas.</p>
                        </div>
                    </div>

                    <form class="mt-5 space-y-4" method="POST" action="{{ route('admin.applications.interviews.store', $application) }}">
                        @csrf
                        <input type="hidden" name="status" value="scheduled">

                        <div>
                            <label class="lumoryx-label" for="new-interview-scheduled-at">Fecha y hora</label>
                            <input class="lumoryx-input mt-2" id="new-interview-scheduled-at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}" required>
                            @error('scheduled_at')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="lumoryx-label" for="new-interview-interviewer">Entrevistador</label>
                            <select class="lumoryx-input mt-2" id="new-interview-interviewer" name="interviewer_id">
                                <option value="">Sin asignar todavia</option>
                                @foreach ($interviewers as $interviewer)
                                    <option value="{{ $interviewer->id }}" @selected((string) old('interviewer_id') === (string) $interviewer->id)>{{ $interviewer->name }} - {{ $interviewer->role->label() }}</option>
                                @endforeach
                            </select>
                            @error('interviewer_id')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="lumoryx-label" for="new-interview-location">Lugar o link</label>
                            <input class="lumoryx-input mt-2" id="new-interview-location" name="location" value="{{ old('location') }}" maxlength="255" placeholder="Discord, canal de voz, Meet, etc.">
                            @error('location')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="lumoryx-label" for="new-interview-notes">Notas para la entrevista</label>
                            <textarea class="lumoryx-input mt-2" id="new-interview-notes" name="notes" rows="3" maxlength="2000" placeholder="Preguntas clave, puntos a revisar o instrucciones.">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-2 text-sm text-rose-200">{{ $message }}</p>@enderror
                        </div>

                        <button class="lumoryx-button-primary w-full" type="submit">Programar entrevista</button>
                    </form>

                    @if ($application->interviews->isNotEmpty())
                        <div class="mt-6 border-t border-white/10 pt-5">
                            <h3 class="font-black text-white">Entrevistas registradas</h3>
                            <div class="mt-3 space-y-3">
                                @foreach ($application->interviews->sortByDesc('scheduled_at') as $interview)
                                    <details class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                                        <summary class="cursor-pointer list-none">
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="min-w-0">
                                                    <p class="truncate font-bold text-white">{{ $interview->scheduled_at?->format('d/m/Y H:i') ?? 'Fecha por definir' }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $interview->interviewer?->name ?? 'Sin entrevistador' }} - {{ $interview->statusLabel() }}</p>
                                                </div>
                                                <span class="text-xs font-black text-amber-100">Editar</span>
                                            </div>
                                        </summary>

                                        <form class="mt-4 space-y-4" method="POST" action="{{ route('admin.applications.interviews.update', [$application, $interview]) }}">
                                            @csrf
                                            @method('PATCH')

                                            <div>
                                                <label class="lumoryx-label" for="interview-{{ $interview->id }}-scheduled-at">Fecha y hora</label>
                                                <input class="lumoryx-input mt-2" id="interview-{{ $interview->id }}-scheduled-at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at', $interview->scheduled_at?->format('Y-m-d\TH:i')) }}" required>
                                            </div>

                                            <div>
                                                <label class="lumoryx-label" for="interview-{{ $interview->id }}-interviewer">Entrevistador</label>
                                                <select class="lumoryx-input mt-2" id="interview-{{ $interview->id }}-interviewer" name="interviewer_id">
                                                    <option value="">Sin asignar</option>
                                                    @foreach ($interviewers as $interviewer)
                                                        <option value="{{ $interviewer->id }}" @selected((int) old('interviewer_id', $interview->interviewer_id) === $interviewer->id)>{{ $interviewer->name }} - {{ $interviewer->role->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="lumoryx-label" for="interview-{{ $interview->id }}-status">Estado</label>
                                                <select class="lumoryx-input mt-2" id="interview-{{ $interview->id }}-status" name="status">
                                                    <option value="scheduled" @selected(old('status', $interview->status) === 'scheduled')>Programada</option>
                                                    <option value="completed" @selected(old('status', $interview->status) === 'completed')>Completada</option>
                                                    <option value="cancelled" @selected(old('status', $interview->status) === 'cancelled')>Cancelada</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="lumoryx-label" for="interview-{{ $interview->id }}-location">Lugar o link</label>
                                                <input class="lumoryx-input mt-2" id="interview-{{ $interview->id }}-location" name="location" value="{{ old('location', $interview->location) }}" maxlength="255">
                                            </div>

                                            <div>
                                                <label class="lumoryx-label" for="interview-{{ $interview->id }}-notes">Notas de preparacion</label>
                                                <textarea class="lumoryx-input mt-2" id="interview-{{ $interview->id }}-notes" name="notes" rows="3" maxlength="2000">{{ old('notes', $interview->notes) }}</textarea>
                                            </div>

                                            <div>
                                                <label class="lumoryx-label" for="interview-{{ $interview->id }}-result-notes">Resultado o notas finales</label>
                                                <textarea class="lumoryx-input mt-2" id="interview-{{ $interview->id }}-result-notes" name="result_notes" rows="3" maxlength="2500" placeholder="Resumen de la entrevista, puntos fuertes o motivo de cancelacion.">{{ old('result_notes', $interview->result_notes) }}</textarea>
                                            </div>

                                            <button class="lumoryx-button-primary w-full" type="submit">Guardar entrevista</button>
                                        </form>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endcan

            <div class="lumoryx-panel p-5">
                <h2 class="text-lg font-bold text-white">Notas internas</h2>
                @can('note', \App\Models\Application::class)
                    <form class="mt-4" method="POST" action="{{ route('admin.applications.notes', $application) }}">
                        @csrf
                        <textarea class="lumoryx-input" name="note" rows="3" required minlength="5" maxlength="2000" placeholder="Solo visible para el equipo.">{{ old('note') }}</textarea>
                        <div class="mt-3 flex justify-end">
                            <button class="lumoryx-button-primary" type="submit">Guardar nota</button>
                        </div>
                    </form>
                @endcan
                <div class="mt-5 space-y-3">
                    @forelse ($application->notes->sortByDesc('created_at')->take(3) as $note)
                        <article class="rounded-lg border border-white/10 bg-white/[.035] p-4">
                            <p class="lumoryx-break whitespace-pre-line text-sm text-slate-300">{{ $note->note }}</p>
                            <p class="mt-3 text-xs text-slate-500">{{ $note->admin?->name ?? 'Admin eliminado' }} - {{ $note->created_at->format('Y-m-d H:i') }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">Sin notas internas.</p>
                    @endforelse
                </div>
            </div>

            <div class="lumoryx-panel p-5">
                <h2 class="text-lg font-bold text-white">Historial del proceso</h2>
                <p class="mt-1 text-sm text-slate-400">Linea de tiempo limpia con estados, entrevistas y mensajes de Discord.</p>
                <x-application-activity-timeline class="mt-5" :items="$timelineItems" />
            </div>
        </div>

        <div class="lumoryx-panel overflow-hidden">
            <div class="border-b border-white/10 p-5">
                <h2 class="text-lg font-bold text-white">Respuestas</h2>
            </div>
            <div class="divide-y divide-white/10">
                @foreach ($application->answers as $answer)
                    <article class="p-5">
                        <h3 class="lumoryx-break text-sm font-semibold text-amber-100">{{ $answer->question }}</h3>
                        <p class="lumoryx-break mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ $answer->answer }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.admin>
