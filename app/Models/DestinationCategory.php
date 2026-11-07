<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DestinationCategory extends Model
{
    use HasFactory, HasUuids, HasTranslations;

    protected $keyType = 'string';
    public $incrementing = false;

    protected array $translatable = [
        'name',
    ];

    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];

    public function destinations()
    {
        return $this->hasMany(Destination::class, 'category_id');
    }
}

