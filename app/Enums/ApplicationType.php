<?php

namespace App\Enums;

enum ApplicationType: string
{
    case Staff = 'staff';
    case Developer = 'developer';
    case Builder = 'builder';
    case Multimedia = 'multimedia';

    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Developer => 'Developer',
            self::Builder => 'Builder',
            self::Multimedia => 'Multimedia',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }
}
