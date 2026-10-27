<?php

namespace App\Enums;

enum ReviewReportStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Terbuka',
            self::Resolved => 'Selesai',
        };
    }
}
