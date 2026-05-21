<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Reviewer = 'reviewer';
    case Admin = 'admin';
    case Owner = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuario',
            self::Reviewer => 'Staff revisor',
            self::Admin => 'Admin',
            self::Owner => 'Owner',
        };
    }

    public function level(): int
    {
        return match ($this) {
            self::User => 10,
            self::Reviewer => 50,
            self::Admin => 80,
            self::Owner => 100,
        };
    }
}
