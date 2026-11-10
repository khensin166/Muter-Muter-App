<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'file_path',
        'caption',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getMediaUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        if (str_starts_with($this->file_path, '/storage/')) {
            return asset(ltrim($this->file_path, '/'));
        }

        if (str_starts_with($this->file_path, 'storage/')) {
            return asset($this->file_path);
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

