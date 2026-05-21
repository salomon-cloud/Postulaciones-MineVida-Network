<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\ApplicationLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class UserPanelController extends Controller
{
    public function notifications(Request $request): View
    {
        $logs = ApplicationLog::query()
            ->with('application')
            ->where('action', '!=', 'internal_note_added')
            ->whereHas('application', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(12);

        $notifications = $this->mapNotifications($logs);

        return view('user.notifications', compact('notifications'));
    }

    public function profile(Request $request): View
    {
        $user = $request->user();
        $applications = $user->applications()
            ->latest()
            ->limit(5)
            ->get();

        return view('user.profile', [
            'user' => $user,
            'applications' => $applications,
            'totalApplications' => $user->applications()->count(),
            'activeApplications' => $user->applications()
                ->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::InReview->value,
                    ApplicationStatus::Interview->value,
                ])
                ->count(),
            'acceptedApplications' => $user->applications()
                ->where('status', ApplicationStatus::Accepted->value)
                ->count(),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('user.settings', [
            'user' => $request->user(),
        ]);
    }

    private function mapNotifications(LengthAwarePaginator $logs): LengthAwarePaginator
    {
        $logs->getCollection()->transform(fn (ApplicationLog $log) => $this->notificationFromLog($log));

        return $logs;
    }

    private function notificationFromLog(ApplicationLog $log): array
    {
        $applicationType = $log->application?->typeLabel() ?? 'tu postulacion';
        $status = $log->new_status ? ApplicationStatus::tryFrom($log->new_status) : null;

        return [
            'title' => match ($log->action) {
                'submitted' => 'Postulacion enviada',
                'status_changed' => $status ? 'Postulacion '.$status->label() : 'Actualizacion de postulacion',
                'cancelled_by_user' => 'Postulacion cancelada',
                'selected_announced' => 'Seleccion anunciado',
                default => 'Actualizacion de postulacion',
            },
            'body' => match ($log->action) {
                'submitted' => 'Hemos recibido tu postulacion para '.$applicationType.'.',
                'status_changed' => $log->description ?: ($status
                    ? 'Tu postulacion para '.$applicationType.' cambio a '.$status->label().'.'
                    : 'Tu postulacion para '.$applicationType.' tuvo una actualizacion.'),
                'cancelled_by_user' => 'Cancelaste tu postulacion para '.$applicationType.'.',
                'selected_announced' => 'Tu postulacion aceptada fue incluida en el anuncio de seleccionados.',
                default => $log->description ?: 'Tu postulacion para '.$applicationType.' tuvo una actualizacion.',
            },
            'application' => $log->application,
            'status' => $status,
            'time' => $log->created_at?->diffForHumans(),
        ];
    }
}
