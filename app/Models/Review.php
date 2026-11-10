<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'user_id',
        'partner_id',
        'product_id',
        'rating',
        'driver_rating',
        'vehicle_rating',
        'comment',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'driver_rating' => 'integer',
            'vehicle_rating' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

