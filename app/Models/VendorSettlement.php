<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSettlement extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'partner_id',
        'booking_id',
        'gross_amount',
        'commission_amount',
        'net_payout_amount',
        'status',
        'bank_reference',
        'eligible_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SettlementStatus::class,
            'gross_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'net_payout_amount' => 'decimal:2',
            'eligible_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

