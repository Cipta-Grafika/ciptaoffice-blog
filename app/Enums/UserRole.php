<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Author = 'author';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Author => 'Author',
        };
    }
}
