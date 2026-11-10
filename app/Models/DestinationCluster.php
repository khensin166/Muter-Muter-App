<?php

namespace App\Models;

use App\Enums\DestinationCluster as DestinationClusterEnum;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DestinationCluster extends Model
{
    use HasFactory, HasUuids, HasTranslations;

    protected $table = 'destination_clusters';
    protected $keyType = 'string';
    public $incrementing = false;

    protected array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'province',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function destinations()
    {
        return $this->hasMany(Destination::class, 'cluster', 'code');
    }

    /**
     * Get active cluster options for dropdowns and filters, falling back to Enum if table is empty.
     */
    public static function activeOptions(): Collection
    {
        try {
            if (Schema::hasTable('destination_clusters')) {
                $records = static::where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->get();

                if ($records->isNotEmpty()) {
                    return $records->map(fn (self $c) => [
                        'value' => $c->code,
                        'label' => $c->name,
                        'description' => $c->description,
                    ])->values();
                }
            }
        } catch (\Throwable) {
            // Fallback to enum below
        }

        return collect(DestinationClusterEnum::cases())->map(fn (DestinationClusterEnum $c) => [
            'value' => $c->value,
            'label' => $c->label(),
            'description' => null,
        ])->values();
    }

    /**
     * Resolve human-readable translated label for any cluster code.
     */
    public static function resolveLabel(?string $code): string
    {
        if (! $code) {
            return '-';
        }

        try {
            if (Schema::hasTable('destination_clusters')) {
                $record = static::where('code', $code)->first();
                if ($record) {
                    return (string) $record->name;
                }
            }
        } catch (\Throwable) {
            // Fallback to enum below
        }

        $enum = DestinationClusterEnum::tryFrom($code);
        if ($enum) {
            return $enum->label();
        }

        return str_replace('_', ' ', ucwords(strtolower($code), '_'));
    }
}

