<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourItineraryDay extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tour_package_id',
        'day_number',
        'title',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
        ];
    }

    public function tourPackage()
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function stops()
    {
        return $this->hasMany(TourItineraryStop::class)->orderBy('stop_order');
    }
}

