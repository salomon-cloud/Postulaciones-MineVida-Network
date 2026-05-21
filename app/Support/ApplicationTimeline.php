<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\ApplicationLog;
use App\Models\DiscordNotification;
use Illuminate\Support\Collection;

class ApplicationTimeline
{
    public static function build(Application $application, bool $includeInternal = false): Collection
    {
        $items = collect();

        foreach ($application->logs as $log) {
            $item = self::fromLog($log, $includeInternal);

            if ($item) {
                $items->push($item);
            }
        }

        foreach ($application->interviews as $interview) {
            $items->push(self::interviewScheduled($interview));

            if ($interview->status === ApplicationInterview::STATUS_COMPLETED && $interview->completed_at) {
                $items->push(self::interviewCompleted($interview));
            }

            if ($interview->status === ApplicationInterview::STATUS_CANCELLED) {
                $items->push(self::interviewCancelled($interview));
            }
        }

        foreach ($application->discordNotifications as $notification) {
            $item = self::fromDiscordNotification($notification, $includeInternal);

            if ($item) {
                $items->push($item);
            }
        }

        return $items
            ->filter(fn (array $item) => $item['time'] !== null)
            ->sortBy('time')
            ->values();
    }

    private static function fromLog(ApplicationLog $log, bool $includeInternal): ?array
    {
        if (! $includeInternal && in_array($log->action, ['internal_note_added'], true)) {
            return null;
        }

        if (str_starts_with($log->action, 'interview_')) {
            return null;
        }

        $status = $log->new_status ? ApplicationStatus::tryFrom($log->new_status) : null;

        return match ($log->action) {
            'submitted' => self::item(
                'Postulacion enviada',
                'Recibimos la solicitud y quedo lista para revision.',
                $log->created_at,
                'send',
                'amber',
                $log->admin?->name,
            ),
            'status_changed' => self::item(
                self::statusTitle($status),
                $log->description ?: self::statusBody($status),
                $log->created_at,
                'status',
                self::statusTone($status),
                $log->admin?->name,
            ),
            'cancelled_by_user' => self::item(
                'Postulacion cancelada',
                'El usuario cancelo el proceso desde su panel.',
                $log->created_at,
                'cancel',
                'slate',
                $log->admin?->name,
            ),
            'selected_announced' => self::item(
                'Seleccion anunciado',
                'La persona fue incluida en el anuncio publico de seleccionados.',
                $log->created_at,
                'dc',
                'emerald',
                $log->admin?->name,
            ),
            default => $includeInternal ? self::item(
                str($log->action)->replace('_', ' ')->title()->toString(),
                $log->description ?: 'Movimiento registrado por el sistema.',
                $log->created_at,
                'log',
                'slate',
                $log->admin?->name,
            ) : null,
        };
    }

    private static function fromDiscordNotification(DiscordNotification $notification, bool $includeInternal): ?array
    {
        if ($notification->status === 'sent' && $notification->sent_at) {
            return self::item(
                'Mensaje enviado por Discord',
                self::discordBody($notification->type),
                $notification->sent_at,
                'dc',
                'discord',
                'Bot',
            );
        }

        if ($includeInternal && $notification->status === 'failed') {
            return self::item(
                'Discord no pudo enviar el mensaje',
                $notification->error_message ?: 'El bot no pudo entregar esta notificacion.',
                $notification->updated_at,
                'dc',
                'rose',
                'Bot',
            );
        }

        return null;
    }

    private static function interviewScheduled(ApplicationInterview $interview): array
    {
        $interviewer = $interview->interviewer?->name ?: 'Entrevistador por asignar';
        $when = $interview->scheduled_at?->format('d/m/Y H:i') ?: 'Fecha por definir';
        $place = $interview->location ? ' Lugar: '.$interview->location.'.' : '';

        return self::item(
            'Entrevista programada',
            'Fecha: '.$when.'. Entrevistador: '.$interviewer.'.'.$place,
            $interview->created_at,
            'call',
            'sky',
            $interview->creator?->name,
        );
    }

    private static function interviewCompleted(ApplicationInterview $interview): array
    {
        return self::item(
            'Entrevista completada',
            $interview->result_notes ?: 'La entrevista fue marcada como completada.',
            $interview->completed_at,
            'ok',
            'emerald',
            $interview->interviewer?->name,
        );
    }

    private static function interviewCancelled(ApplicationInterview $interview): array
    {
        return self::item(
            'Entrevista cancelada',
            $interview->result_notes ?: 'La entrevista fue cancelada o quedo sin efecto.',
            $interview->updated_at,
            'cancel',
            'rose',
            $interview->interviewer?->name,
        );
    }

    private static function item(string $title, string $body, mixed $time, string $icon, string $tone, ?string $actor = null): array
    {
        return compact('title', 'body', 'time', 'icon', 'tone', 'actor');
    }

    private static function statusTitle(?ApplicationStatus $status): string
    {
        return match ($status) {
            ApplicationStatus::Pending => 'Marcada como pendiente',
            ApplicationStatus::InReview => 'Paso a revision',
            ApplicationStatus::Interview => 'Paso a entrevista',
            ApplicationStatus::Accepted => 'Postulacion aceptada',
            ApplicationStatus::Rejected => 'Postulacion rechazada',
            ApplicationStatus::Cancelled => 'Postulacion cancelada',
            default => 'Estado actualizado',
        };
    }

    private static function statusBody(?ApplicationStatus $status): string
    {
        return match ($status) {
            ApplicationStatus::Pending => 'La solicitud queda pendiente de revision.',
            ApplicationStatus::InReview => 'El equipo esta revisando las respuestas y el perfil.',
            ApplicationStatus::Interview => 'El equipo continuara el proceso con una entrevista.',
            ApplicationStatus::Accepted => 'La solicitud fue aprobada por el equipo.',
            ApplicationStatus::Rejected => 'El equipo decidio no continuar con esta solicitud.',
            ApplicationStatus::Cancelled => 'El proceso quedo cancelado.',
            default => 'La postulacion tuvo una actualizacion.',
        };
    }

    private static function statusTone(?ApplicationStatus $status): string
    {
        return match ($status) {
            ApplicationStatus::Accepted => 'emerald',
            ApplicationStatus::Rejected => 'rose',
            ApplicationStatus::Interview => 'sky',
            ApplicationStatus::InReview => 'amber',
            ApplicationStatus::Cancelled => 'slate',
            default => 'slate',
        };
    }

    private static function discordBody(string $type): string
    {
        return match (true) {
            str_starts_with($type, 'dm_') => 'Se envio una notificacion privada con el estado actualizado.',
            $type === 'staff_new_application' => 'El equipo recibio el aviso interno de nueva postulacion.',
            default => 'Se envio una notificacion por Discord.',
        };
    }
}
