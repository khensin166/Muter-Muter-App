<?php

namespace App\Enums;

enum DestinationCluster: string
{
    case PARAPAT = 'PARAPAT';
    case SAMOSIR_EAST = 'SAMOSIR_EAST';
    case SAMOSIR_WEST = 'SAMOSIR_WEST';
    case MEREK_TONGGING = 'MEREK_TONGGING';
    case MEDAN_HUB = 'MEDAN_HUB';

    public function label(): string
    {
        return match ($this) {
            self::PARAPAT => 'Parapat (Gerbang Utama & Pelabuhan)',
            self::SAMOSIR_EAST => 'Samosir Timur (Tomok, Ambarita, Tuktuk)',
            self::SAMOSIR_WEST => 'Samosir Barat (Pangururan & Holbung)',
            self::MEREK_TONGGING => 'Merek & Tongging (Sipiso-piso & Kaldera Utara)',
            self::MEDAN_HUB => 'Medan Hub (Titik Keberangkatan & Bandara)',
        };
    }
}

