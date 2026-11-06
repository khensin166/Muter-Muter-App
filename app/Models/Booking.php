<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_code',
        'user_id',
        'product_id',
        'partner_id',
        'booking_status',
        'payment_status',
        'inventory_status',
        'service_start_date',
        'service_end_date',
        'service_start_time',
        'total_price',
        'dp_amount',
        'total_paid',
        'commission_percentage',
        'platform_commission_amount',
        'partner_payout_amount',
        'product_snapshot',
        'pricing_breakdown',
        'cancellation_policy_snapshot',
        'vendor_sla_deadline',
    ];

    protected function casts(): array
    {
        return [
            'booking_status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'inventory_status' => InventoryStatus::class,
            'service_start_date' => 'date',
            'service_end_date' => 'date',
            'vendor_sla_deadline' => 'datetime',
            'total_price' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'platform_commission_amount' => 'decimal:2',
            'partner_payout_amount' => 'decimal:2',
            'product_snapshot' => 'array',
            'pricing_breakdown' => 'array',
            'cancellation_policy_snapshot' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function tourDetail()
    {
        return $this->hasOne(BookingTourDetail::class);
    }

    public function vehicleDetail()
    {
        return $this->hasOne(BookingVehicleDetail::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class)->orderBy('created_at');
    }

    public function inventoryHolds()
    {
        return $this->hasMany(InventoryHold::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function settlement()
    {
        return $this->hasOne(VendorSettlement::class);
    }
}

