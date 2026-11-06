<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourDeparture extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tour_package_id',
        'departure_date',
        'departure_time',
        'max_quota',
        'booked_quota',
        'price_override',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'max_quota' => 'integer',
            'booked_quota' => 'integer',
            'price_override' => 'decimal:2',
        ];
    }

    public function tourPackage()
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function inventoryHolds()
    {
        return $this->hasMany(InventoryHold::class);
    }
}

