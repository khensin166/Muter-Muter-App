<?php

namespace App\Models;

use App\Enums\PartnerStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'business_name',
        'brand_name',
        'pic_name',
        'pic_phone',
        'email',
        'address',
        'city',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
    ];

    protected function casts(): array
    {
        return [
            'status' => PartnerStatus::class,
        ];
    }

    public function members()
    {
        return $this->hasMany(PartnerMember::class);
    }

    public function documents()
    {
        return $this->hasMany(PartnerDocument::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function settlements()
    {
        return $this->hasMany(VendorSettlement::class);
    }
}

