<?php

namespace App\Models;

use App\Enums\DestinationCluster;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

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
    ];

    protected function casts(): array
    {
        return [
            'cluster' => DestinationCluster::class,
            'is_featured' => 'boolean',
            'ticket_price_domestic' => 'decimal:2',
            'ticket_price_foreign' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
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

