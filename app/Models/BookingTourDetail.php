<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTourDetail extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'tour_departure_id',
        'participant_count',
        'special_requests',
    ];

    protected function casts(): array
    {
        return [
            'participant_count' => 'integer',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function departure()
    {
        return $this->belongsTo(TourDeparture::class, 'tour_departure_id');
    }
}

