<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;

class PartnerPolicy
{
    /**
     * Determine whether the user can view the partner profile.
     */
    public function view(User $user, Partner $partner): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->partnerMemberships()->where('partner_id', $partner->id)->exists();
    }

    /**
     * Determine whether the user can update the partner profile.
     */
    public function update(User $user, Partner $partner): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPartnerOwner($partner);
    }

    /**
     * Determine whether the user can manage staff for the partner.
     */
    public function manageStaff(User $user, Partner $partner): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPartnerOwner($partner);
    }

    /**
     * Determine whether the user can manage bank accounts for payouts.
     */
    public function manageBank(User $user, Partner $partner): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPartnerOwner($partner);
    }

    /**
     * Determine whether the user can view financial settlements.
     */
    public function viewSettlements(User $user, Partner $partner): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPartnerOwner($partner);
    }

    /**
     * Determine whether the user can respond to incoming booking requests.
     */
    public function respondBooking(User $user, Partner $partner): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->partnerMemberships()->where('partner_id', $partner->id)->exists();
    }
}

