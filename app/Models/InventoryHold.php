<?php

namespace App\Models;

use App\Enums\InventoryStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryHold extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'product_id',
        'tour_departure_id',
        'vehicle_id',
        'held_quantity',
        'held_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => InventoryStatus::class,
            'held_at' => 'datetime',
            'expires_at' => 'datetime',
            'held_quantity' => 'integer',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function departure()
    {
        return $this->belongsTo(TourDeparture::class, 'tour_departure_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

