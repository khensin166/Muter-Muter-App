<?php

namespace App\Models;

use App\Enums\TourType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'tour_type',
        'duration_days',
        'duration_nights',
        'min_participants',
        'max_participants',
        'inclusions',
        'exclusions',
        'terms_conditions',
        'includes_ferry',
    ];

    protected function casts(): array
    {
        return [
            'tour_type' => TourType::class,
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
            'min_participants' => 'integer',
            'max_participants' => 'integer',
            'includes_ferry' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function itineraryDays()
    {
        return $this->hasMany(TourItineraryDay::class)->orderBy('day_number');
    }

    public function departures()
    {
        return $this->hasMany(TourDeparture::class);
    }
}

