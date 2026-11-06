<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\OwnershipType;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'partner_id',
        'ownership_type',
        'product_type',
        'name',
        'slug',
        'short_description',
        'full_description',
        'base_price',
        'pricing_type',
        'dp_percentage',
        'min_lead_time_hours',
        'status',
        'approval_status',
    ];

    protected function casts(): array
    {
        return [
            'ownership_type' => OwnershipType::class,
            'product_type' => ProductType::class,
            'pricing_type' => PricingType::class,
            'status' => ProductStatus::class,
            'approval_status' => ApprovalStatus::class,
            'base_price' => 'decimal:2',
            'dp_percentage' => 'decimal:2',
            'min_lead_time_hours' => 'integer',
        ];
    }

    public function isPublishedAndApproved(): bool
    {
        return $this->status === ProductStatus::PUBLISHED && $this->approval_status === ApprovalStatus::APPROVED;
    }

    public function scopePublishedAndApproved($query)
    {
        return $query->where('status', ProductStatus::PUBLISHED)
            ->where('approval_status', ApprovalStatus::APPROVED);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function tourPackage()
    {
        return $this->hasOne(TourPackage::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(ProductApprovalLog::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function inventoryHolds()
    {
        return $this->hasMany(InventoryHold::class);
    }
}

