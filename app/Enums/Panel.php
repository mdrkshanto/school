<?php

namespace App\Enums;

enum Panel: string
{
    case Admin = 'admin';

    case Teacher = 'teacher';

    case Student = 'student';

    case Parent = 'parent';

    public function role(): string
    {
        return match ($this) {
            self::Admin => 'admin',
            self::Teacher => 'teacher',
            self::Student => 'student',
            self::Parent => 'parent',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Teacher => 'Teacher',
            self::Student => 'Student',
            self::Parent => 'Parent',
        };
    }

    public static function loginRoute(Panel $panel): string
    {
        return "panel.{$panel->value}.login";
    }

    public static function fromRoute(string $prefix): self
    {
        return self::tryFrom($prefix) ?? self::Admin;
    }
}