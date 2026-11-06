<?php

namespace App\Models;

use App\Enums\PartnerRole;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN || $this->role === UserRole::SUPER_ADMIN;
    }

    public function isPartner(): bool
    {
        return $this->role === UserRole::PARTNER;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function partnerMemberships()
    {
        return $this->hasMany(PartnerMember::class);
    }

    public function currentPartnerMembership(): ?PartnerMember
    {
        return $this->partnerMemberships()->where('is_active', true)->with('partner')->first();
    }

    public function currentPartner(): ?Partner
    {
        return $this->currentPartnerMembership()?->partner;
    }

    public function partnerRole(): ?PartnerRole
    {
        return $this->currentPartnerMembership()?->role;
    }

    public function isPartnerOwner(Partner $partner): bool
    {
        $membership = $this->partnerMemberships()->where('partner_id', $partner->id)->first();
        return $membership && $membership->role === PartnerRole::OWNER;
    }

    public function isPartnerStaff(Partner $partner): bool
    {
        $membership = $this->partnerMemberships()->where('partner_id', $partner->id)->first();
        return $membership && $membership->role === PartnerRole::STAFF;
    }

    public function getPartnerData(): ?array
    {
        $partner = $this->currentPartner();
        if (!$partner) {
            return null;
        }

        return [
            'id' => $partner->id,
            'business_name' => $partner->business_name,
            'brand_name' => $partner->brand_name,
            'status' => $partner->status->value,
            'role' => $this->partnerRole()?->value,
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
