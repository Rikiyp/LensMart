<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case OutOfStock = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
            self::OutOfStock => 'Stok Habis',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Inactive => 'bg-slate-100 text-slate-600 ring-slate-500/20',
            self::OutOfStock => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        };
    }
}
