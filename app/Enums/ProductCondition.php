<?php

namespace App\Enums;

enum ProductCondition: string
{
    case New = 'new';
    case LikeNew = 'like_new';
    case Good = 'good';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baru',
            self::LikeNew => 'Bekas Mulus',
            self::Good => 'Bekas Normal',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::LikeNew => 'bg-violet-50 text-violet-700 ring-violet-600/20',
            self::Good => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        };
    }
}
