<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Returned = 'returned';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Menunggu review',
            self::Returned => 'Dikembalikan',
            self::Published => 'Terbit',
            self::Archived => 'Diarsipkan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::PendingReview => 'warning',
            self::Returned => 'danger',
            self::Published => 'success',
            self::Archived => 'dark',
        };
    }
}
