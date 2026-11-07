<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourItineraryDay extends Model
{
    use HasFactory, HasUuids, HasTranslations;

    protected $keyType = 'string';
    public $incrementing = false;

    protected array $translatable = [
        'title',
        'description',
    ];

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

