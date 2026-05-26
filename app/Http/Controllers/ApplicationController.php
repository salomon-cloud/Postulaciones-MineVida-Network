<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\Setting;
use App\Services\DiscordNotificationService;
use App\Services\DiscordSystemLogService;
use App\Support\ApplicationTimeline;
use App\Support\ApplicationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = $request->user()
            ->applications()
            ->with([
                'answers',
                'interviews' => fn ($query) => $query->latest('scheduled_at'),
            ])
            ->latest()
            ->paginate(10);

        $recentNotifications = ApplicationLog::query()
            ->with('application')
            ->where('action', '!=', 'internal_note_added')
            ->whereHas('application', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (ApplicationLog $log) => $this->notificationFromLog($log));

        return view('applications.index', compact('applications', 'recentNotifications'));
    }

    public function create(): View
    {
        $categories = ApplicationCatalog::categories(true);
        $types = ApplicationCatalog::types(true);

        if ($categories->isNotEmpty() || $types === []) {
            return view('applications.create', [
                'types' => $categories->map(fn ($category) => [
                    'type' => $category->slug,
                    'label' => $category->name,
                    'summary' => $category->summary,
                    'icon' => $category->icon,
                    'image_url' => $category->imageUrl(),
                    'is_open' => $category->is_open,
                    'closed_until' => $category->closed_until,
                    'closed_message' => $category->closed_message,
                ]),
            ]);
        }

        return view('applications.create', [
            'types' => collect(ApplicationCatalog::types())->map(fn ($label, $type) => [
                'type' => $type,
                'label' => $label,
                'summary' => ApplicationCatalog::definition($type)['summary'],
                'icon' => ApplicationCatalog::definition($type)['icon'],
                'image_url' => null,
                'is_open' => true,
                'closed_until' => null,
                'closed_message' => null,
            ]),
        ]);
    }

    public function createType(string $type): View
    {
        $category = ApplicationCatalog::category($type, true);

        if ($category && ! $category->is_open) {
            return view('applications.closed', [
                'category' => $category,
            ]);
        }

        return view('applications.form', [
            'type' => $type,
            'definition' => ApplicationCatalog::definition($type),
            'minimumAge' => ApplicationCatalog::minimumAge($type),
        ]);
    }

    public function store(
        StoreApplicationRequest $request,
        DiscordNotificationService $discord,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $application = DB::transaction(function () use ($request) {
            $application = $request->user()->applications()->create([
                ...$request->applicationData(),
                'status' => ApplicationStatus::Pending,
            ]);

            foreach ($request->answerData() as $question => $answer) {
                $application->answers()->create([
                    'question' => $question,
                    'answer' => $answer,
                ]);
            }

            $application->logs()->create([
                'action' => 'submitted',
                'new_status' => ApplicationStatus::Pending->value,
                'description' => 'Postulacion enviada por el usuario.',
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
            ]);

            return $application->load('user');
        });

        $discord->queueStaffApplication($application);
        $systemLogs->logApplicationEvent(
            'applications',
            'Nueva postulacion enviada',
            'Un usuario envio una nueva solicitud y quedo pendiente de revision.',
            $application,
            'warning',
            $request->user(),
            $request,
        );

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Tu postulacion fue enviada y quedo pendiente de revision.');
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        return view('applications.show', [
            'application' => $application->load([
                'answers',
                'logs.admin',
                'interviews.interviewer',
                'discordNotifications',
            ]),
            'timelineItems' => ApplicationTimeline::build($application),
        ]);
    }

    public function cancel(Request $request, Application $application, DiscordSystemLogService $systemLogs): RedirectResponse
    {
        $this->authorize('cancel', $application);

        $oldStatus = $application->status;

        $application->update([
            'status' => ApplicationStatus::Cancelled,
        ]);

        $application->logs()->create([
            'admin_id' => null,
            'action' => 'cancelled_by_user',
            'old_status' => $oldStatus->value,
            'new_status' => ApplicationStatus::Cancelled->value,
            'description' => 'Postulacion cancelada por el usuario.',
            'ip_address' => $request->ip(),
            'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
        ]);

        $application->refresh()->load('user');
        $systemLogs->logApplicationEvent(
            'applications',
            'Postulacion cancelada por el usuario',
            'El usuario cancelo su postulacion desde el panel.',
            $application,
            'danger',
            $request->user(),
            $request,
            ['Estado anterior' => $oldStatus->label()],
        );

        return redirect()->route('applications.show', $application)->with('success', 'Postulacion cancelada.');
    }

    private function notificationFromLog(ApplicationLog $log): array
    {
        $applicationType = $log->application?->typeLabel() ?? 'tu postulacion';
        $status = $log->new_status ? ApplicationStatus::tryFrom($log->new_status) : null;

        return [
            'title' => match ($log->action) {
                'submitted' => 'Gracias por tu postulacion',
                'status_changed' => $status ? 'Postulacion '.$status->label() : 'Actualizacion de postulacion',
                'cancelled_by_user' => 'Postulacion cancelada',
                'interview_scheduled' => 'Entrevista programada',
                'interview_completed' => 'Entrevista completada',
                'interview_cancelled' => 'Entrevista cancelada',
                default => 'Actualizacion de postulacion',
            },
            'body' => match ($log->action) {
                'submitted' => 'Hemos recibido tu postulacion para '.$applicationType.'.',
                'status_changed' => $log->description ?: ($status
                    ? 'Tu postulacion para '.$applicationType.' cambio a '.$status->label().'.'
                    : 'Tu postulacion para '.$applicationType.' tuvo una actualizacion.'),
                'cancelled_by_user' => 'Cancelaste tu postulacion para '.$applicationType.'.',
                'interview_scheduled' => $log->description ?: 'El equipo programo una entrevista para '.$applicationType.'.',
                'interview_completed' => $log->description ?: 'La entrevista de '.$applicationType.' fue marcada como completada.',
                'interview_cancelled' => $log->description ?: 'La entrevista de '.$applicationType.' fue cancelada.',
                default => $log->description ?: 'Tu postulacion para '.$applicationType.' tuvo una actualizacion.',
            },
            'time' => $log->created_at?->diffForHumans(),
        ];
    }
}
