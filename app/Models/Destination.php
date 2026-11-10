<?php

namespace App\Models;

use App\Casts\DestinationClusterCast;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory, HasUuids, HasTranslations;

    protected $keyType = 'string';
    public $incrementing = false;

    protected array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'cluster',
        'description',
        'latitude',
        'longitude',
        'ticket_price_domestic',
        'ticket_price_foreign',
        'operating_hours',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cluster' => DestinationClusterCast::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'ticket_price_domestic' => 'decimal:2',
            'ticket_price_foreign' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function clusterRecord()
    {
        return $this->belongsTo(DestinationCluster::class, 'cluster', 'code');
    }

    public function category()
    {
        return $this->belongsTo(DestinationCategory::class, 'category_id');
    }

    public function media()
    {
        return $this->hasMany(DestinationMedia::class);
    }

    public function itineraryStops()
    {
        return $this->hasMany(TourItineraryStop::class);
    }
}

