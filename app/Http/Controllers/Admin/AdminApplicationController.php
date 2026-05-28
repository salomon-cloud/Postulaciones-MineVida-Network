<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationNoteRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Requests\UpsertApplicationInterviewRequest;
use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\Setting;
use App\Models\User;
use App\Services\DiscordNotificationService;
use App\Services\DiscordSystemLogService;
use App\Support\ApplicationTimeline;
use App\Support\ApplicationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAdmin', Application::class);

        $stats = [
            'pending' => Application::query()->where('status', ApplicationStatus::Pending->value)->count(),
            'accepted' => Application::query()->where('status', ApplicationStatus::Accepted->value)->count(),
            'rejected' => Application::query()->where('status', ApplicationStatus::Rejected->value)->count(),
            'interview' => Application::query()->where('status', ApplicationStatus::Interview->value)->count(),
        ];

        $applications = Application::query()
            ->with('user')
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($this->validDateFilter($request, 'from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($this->validDateFilter($request, 'to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('user'), function ($query) use ($request) {
                $search = '%'.$request->string('user')->toString().'%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('minecraft_nick', 'like', $search)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('discord_username', 'like', $search)
                            ->orWhere('discord_global_name', 'like', $search)
                            ->orWhere('discord_id', 'like', $search));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'types' => ApplicationCatalog::types(true),
            'statuses' => ApplicationStatus::cases(),
            'filters' => $request->only(['type', 'status', 'from', 'to', 'user']),
            'stats' => $stats,
        ]);
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        return view('admin.applications.show', [
            'application' => $application->load([
                'user',
                'answers',
                'notes.admin',
                'logs.admin',
                'interviews.interviewer',
                'interviews.creator',
                'discordNotifications',
            ]),
            'statuses' => ApplicationStatus::cases(),
            'interviewers' => User::query()
                ->whereIn('role', [
                    UserRole::Reviewer->value,
                    UserRole::Admin->value,
                    UserRole::Owner->value,
                ])
                ->orderBy('name')
                ->get(),
            'timelineItems' => ApplicationTimeline::build($application, includeInternal: true),
        ]);
    }

    public function updateStatus(
        UpdateApplicationStatusRequest $request,
        Application $application,
        DiscordNotificationService $discord,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse {
        $this->authorize('updateStatus', Application::class);

        $newStatus = ApplicationStatus::from($request->validated('status'));
        $oldStatus = $application->status;

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'La postulacion ya tenia ese estado.');
        }

        DB::transaction(function () use ($request, $application, $newStatus, $oldStatus) {
            $cooldownUntil = $newStatus === ApplicationStatus::Rejected
                ? now()->addDays(Setting::integer('reapply_cooldown_days', 14))
                : $application->cooldown_until;

            $application->update([
                'status' => $newStatus,
                'admin_response' => $request->validated('admin_response'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'cooldown_until' => $cooldownUntil,
                'correction_requested' => false,
            ]);

            $application->logs()->create([
                'admin_id' => $request->user()->id,
                'action' => 'status_changed',
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'description' => $request->validated('admin_response'),
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
            ]);
        });

        $application->refresh()->load('user');
        $discord->queueStatusDm($application);
        $systemLogs->logApplicationEvent(
            'status',
            'Estado de postulacion actualizado',
            'Un administrador cambio el estado de una postulacion.',
            $application,
            $newStatus->isFinal() ? ($newStatus === ApplicationStatus::Accepted ? 'success' : 'danger') : 'warning',
            $request->user(),
            $request,
            [
                'Cambio' => $oldStatus->label().' -> '.$newStatus->label(),
                'Respuesta' => $request->validated('admin_response') ?: 'Sin respuesta personalizada.',
            ],
        );

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function storeInterview(
        UpsertApplicationInterviewRequest $request,
        Application $application,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $this->authorize('updateStatus', Application::class);

        $validated = $request->validated();
        $oldStatus = $application->status;

        $interview = DB::transaction(function () use ($request, $application, $validated, $oldStatus) {
            $interview = $application->interviews()->create([
                ...$validated,
                'created_by' => $request->user()->id,
                'completed_at' => $validated['status'] === ApplicationInterview::STATUS_COMPLETED ? now() : null,
            ]);

            if (! $application->status->isFinal()) {
                $application->update([
                    'status' => ApplicationStatus::Interview,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'correction_requested' => false,
                ]);
            }

            $application->logs()->create([
                'admin_id' => $request->user()->id,
                'action' => 'interview_scheduled',
                'old_status' => $oldStatus->value,
                'new_status' => ApplicationStatus::Interview->value,
                'description' => 'Entrevista programada para '.$interview->scheduled_at?->format('d/m/Y H:i').'.',
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
            ]);

            return $interview;
        });

        $application->refresh()->load('user');
        $systemLogs->logApplicationEvent(
            'interviews',
            'Entrevista programada',
            'Se programo una entrevista para una postulacion.',
            $application,
            'discord',
            $request->user(),
            $request,
            [
                'Fecha' => $interview->scheduled_at?->format('d/m/Y H:i') ?: '-',
                'Lugar' => $interview->location ?: '-',
                'Entrevistador' => $interview->interviewer?->name ?: 'Sin asignar',
            ],
        );

        return back()->with('success', 'Entrevista programada correctamente.');
    }

    public function updateInterview(
        UpsertApplicationInterviewRequest $request,
        Application $application,
        ApplicationInterview $interview,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse {
        $this->authorize('updateStatus', Application::class);

        abort_unless($interview->application_id === $application->id, 404);

        $validated = $request->validated();
        $oldStatus = $interview->status;

        DB::transaction(function () use ($request, $application, $interview, $validated, $oldStatus) {
            $interview->update([
                ...$validated,
                'completed_at' => $validated['status'] === ApplicationInterview::STATUS_COMPLETED
                    ? ($interview->completed_at ?? now())
                    : null,
            ]);

            $action = match ($validated['status']) {
                ApplicationInterview::STATUS_COMPLETED => 'interview_completed',
                ApplicationInterview::STATUS_CANCELLED => 'interview_cancelled',
                default => 'interview_updated',
            };

            $application->logs()->create([
                'admin_id' => $request->user()->id,
                'action' => $action,
                'old_status' => $application->status->value,
                'new_status' => $application->status->value,
                'description' => $validated['result_notes'] ?: 'Entrevista '.$oldStatus.' -> '.$validated['status'].'.',
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
            ]);
        });

        $application->refresh()->load('user');
        $interview->refresh();
        $systemLogs->logApplicationEvent(
            'interviews',
            'Entrevista actualizada',
            'Se actualizo el estado o informacion de una entrevista.',
            $application,
            match ($interview->status) {
                ApplicationInterview::STATUS_COMPLETED => 'success',
                ApplicationInterview::STATUS_CANCELLED => 'danger',
                default => 'discord',
            },
            $request->user(),
            $request,
            [
                'Cambio entrevista' => $oldStatus.' -> '.$interview->status,
                'Fecha' => $interview->scheduled_at?->format('d/m/Y H:i') ?: '-',
                'Notas' => $interview->result_notes ?: 'Sin notas.',
            ],
        );

        return back()->with('success', 'Entrevista actualizada correctamente.');
    }

    public function storeNote(
        StoreApplicationNoteRequest $request,
        Application $application,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $this->authorize('note', Application::class);

        $application->notes()->create([
            'admin_id' => $request->user()->id,
            'note' => $request->validated('note'),
        ]);

        $application->logs()->create([
            'admin_id' => $request->user()->id,
            'action' => 'internal_note_added',
            'description' => 'Nota interna agregada.',
            'ip_address' => $request->ip(),
            'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
        ]);

        $application->load('user');
        $systemLogs->logApplicationEvent(
            'applications',
            'Nota interna agregada',
            'Un miembro del equipo agrego una nota interna a una postulacion.',
            $application,
            'info',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Nota interna guardada.');
    }

    public function destroy(
        Request $request,
        Application $application,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse {
        $this->authorize('delete', $application);

        $application->load('user');

        DB::transaction(function () use ($request, $application) {
            $application->logs()->create([
                'admin_id' => $request->user()->id,
                'action' => 'application_deleted',
                'old_status' => $application->status->value,
                'new_status' => $application->status->value,
                'description' => 'Postulacion eliminada del panel administrativo.',
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
            ]);

            $application->delete();
        });

        $systemLogs->queue(
            'applications',
            'Postulacion eliminada',
            'Un administrador elimino una postulacion del panel.',
            [
                'Usuario' => ($application->user?->discord_username ?? 'Sin usuario').' ('.$application->user?->discord_id.')',
                'Minecraft' => $application->minecraft_nick,
                'Categoria' => $application->typeLabel(),
                'Estado anterior' => $application->status->label(),
            ],
            'danger',
            $request->user(),
            $request,
        );

        return redirect()
            ->route('admin.applications.index')
            ->with('success', 'Postulacion eliminada correctamente.');
    }

    private function validDateFilter(Request $request, string $key): bool
    {
        return $request->filled($key) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input($key));
    }
}
