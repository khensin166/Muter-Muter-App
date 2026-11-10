<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourItineraryStop extends Model
{
    use HasFactory, HasUuids, HasTranslations;

    protected $keyType = 'string';
    public $incrementing = false;

    protected array $translatable = [
        'title',
        'activity_description',
    ];

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

    public function day()
    {
        return $this->itineraryDay();
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }
}

