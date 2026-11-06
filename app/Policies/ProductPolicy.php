<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPartner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Product $product): bool
    {
        // Public can view if published and approved
        if ($product->isPublishedAndApproved()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $partner = $user->currentPartner();

        return $partner && $product->partner_id === $partner->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPartner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $partner = $user->currentPartner();

        return $partner && $product->partner_id === $partner->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $partner = $user->currentPartner();

        return $partner && $product->partner_id === $partner->id;
    }

    /**
     * Determine whether the user can moderate (approve/reject) the product.
     */
    public function moderate(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}

