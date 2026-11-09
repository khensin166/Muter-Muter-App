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
        if (app()->getLocale() === 'en') {
            return match ($this) {
                self::PARAPAT => 'Parapat (Main Gateway & Harbor)',
                self::SAMOSIR_EAST => 'East Samosir (Tomok, Ambarita, Tuktuk)',
                self::SAMOSIR_WEST => 'West Samosir (Pangururan & Holbung)',
                self::MEREK_TONGGING => 'Merek & Tongging (Sipiso-piso & North Caldera)',
                self::MEDAN_HUB => 'Medan Hub (Departure Point & Airport)',
            };
        }

        return match ($this) {
            self::PARAPAT => 'Parapat (Gerbang Utama & Pelabuhan)',
            self::SAMOSIR_EAST => 'Samosir Timur (Tomok, Ambarita, Tuktuk)',
            self::SAMOSIR_WEST => 'Samosir Barat (Pangururan & Holbung)',
            self::MEREK_TONGGING => 'Merek & Tongging (Sipiso-piso & Kaldera Utara)',
            self::MEDAN_HUB => 'Medan Hub (Titik Keberangkatan & Bandara)',
        };
    }
}

