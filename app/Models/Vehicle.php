<?php

namespace App\Models;

use App\Enums\TransmissionType;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'vehicle_type',
        'brand',
        'model',
        'manufacturing_year',
        'passenger_capacity',
        'luggage_capacity',
        'transmission',
        'with_driver',
        'license_plate',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'transmission' => TransmissionType::class,
            'with_driver' => 'boolean',
            'manufacturing_year' => 'integer',
            'passenger_capacity' => 'integer',
            'luggage_capacity' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unavailabilities()
    {
        return $this->hasMany(VehicleUnavailability::class);
    }
}

