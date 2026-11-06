<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'calculated_refund_percentage',
        'refund_amount',
        'reason',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
            'calculated_refund_percentage' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function processedByAdmin()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

