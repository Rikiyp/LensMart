<?php

namespace App\Enums;

enum OrderStatus: string
{
    case WaitingConfirmation = 'waiting_confirmation';
    case Confirmed = 'confirmed';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WaitingConfirmation => 'Menunggu Konfirmasi',
            self::Confirmed => 'Dikonfirmasi',
            self::Packed => 'Dikemas',
            self::Shipped => 'Dikirim',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::WaitingConfirmation => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Confirmed => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::Packed => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            self::Shipped => 'bg-violet-50 text-violet-700 ring-violet-600/20',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Cancelled => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::WaitingConfirmation => self::Confirmed,
            self::Confirmed => self::Packed,
            self::Packed => self::Shipped,
            self::Shipped => self::Completed,
            self::Completed, self::Cancelled => null,
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this->next() === $target;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function isCancellable(): bool
    {
        return $this === self::WaitingConfirmation;
    }

    /**
     * @return array<int, self>
     */
    public static function progressSteps(): array
    {
        return [self::WaitingConfirmation, self::Confirmed, self::Packed, self::Shipped, self::Completed];
    }
}
