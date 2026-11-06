<?php

namespace App\Enums;

enum InventoryStatus: string
{
    case NONE = 'NONE';
    case HELD = 'HELD';
    case ALLOCATED = 'ALLOCATED';
    case RELEASED = 'RELEASED';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Tidak Ada Alokasi',
            self::HELD => 'Ditahan Sementara (Hold 24 Jam)',
            self::ALLOCATED => 'Teralokasi Pasti',
            self::RELEASED => 'Dilepas ke Publik',
        };
    }
}

