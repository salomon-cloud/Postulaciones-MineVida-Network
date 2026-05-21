<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case Interview = 'interview';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InReview => 'En revision',
            self::Interview => 'Entrevista',
            self::Accepted => 'Aceptada',
            self::Rejected => 'Rechazada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-slate-400/10 text-slate-200 ring-slate-400/25',
            self::InReview => 'bg-amber-400/10 text-amber-200 ring-amber-400/25',
            self::Interview => 'bg-sky-400/10 text-sky-200 ring-sky-400/25',
            self::Accepted => 'bg-emerald-400/10 text-emerald-200 ring-emerald-400/25',
            self::Rejected => 'bg-rose-400/10 text-rose-200 ring-rose-400/25',
            self::Cancelled => 'bg-zinc-400/10 text-zinc-300 ring-zinc-400/25',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::InReview, self::Interview], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Accepted, self::Rejected, self::Cancelled], true);
    }

    public function userSummary(): string
    {
        return match ($this) {
            self::Pending => 'Tu solicitud fue recibida y espera revision inicial.',
            self::InReview => 'El equipo esta leyendo tus respuestas y evaluando tu perfil.',
            self::Interview => 'Tu proceso avanzo a entrevista. Revisa fecha, hora e indicaciones.',
            self::Accepted => 'Felicidades, el equipo aprobo tu postulacion.',
            self::Rejected => 'Esta vez no fue aceptada, pero puedes volver a intentarlo cuando termine el cooldown.',
            self::Cancelled => 'Esta postulacion fue cancelada y ya no esta en revision.',
        };
    }

    public function nextStep(): string
    {
        return match ($this) {
            self::Pending => 'Espera a que un administrador la pase a revision.',
            self::InReview => 'Mantente atento a Discord por si el equipo necesita contactarte.',
            self::Interview => 'Preparate para la entrevista y manten tus mensajes abiertos.',
            self::Accepted => 'Espera las instrucciones del equipo para tu integracion.',
            self::Rejected => 'Revisa el mensaje del equipo, mejora tu perfil y postulate de nuevo cuando puedas.',
            self::Cancelled => 'Puedes crear otra postulacion si la categoria esta abierta y cumples los requisitos.',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $status) => $status->value, self::cases());
    }
}
