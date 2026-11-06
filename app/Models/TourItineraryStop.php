<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourItineraryStop extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tour_itinerary_day_id',
        'destination_id',
        'stop_order',
        'title',
        'activity_description',
        'estimated_minutes',
    ];

    protected function casts(): array
    {
        return [
            'stop_order' => 'integer',
            'estimated_minutes' => 'integer',
        ];
    }

    public function itineraryDay()
    {
        return $this->belongsTo(TourItineraryDay::class, 'tour_itinerary_day_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }
}

