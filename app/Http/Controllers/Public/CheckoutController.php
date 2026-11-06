<?php

namespace App\Http\Controllers\Public;

use App\Actions\Booking\CreateBookingAction;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    /**
     * Display the checkout configuration page.
     */
    public function create(Request $request): Response
    {
        $productId = $request->query('product_id');
        $participants = (int) $request->query('participants', 2);
        $rentalDays = (int) $request->query('rental_days', 1);

        $product = Product::query()
            ->publishedAndApproved()
            ->with(['tourPackage', 'vehicle', 'partner', 'media'])
            ->findOrFail($productId);

        $quantity = $product->product_type === ProductType::TOUR ? $participants : $rentalDays;
        $totalPrice = (float) $product->base_price * $quantity;
        $dpAmount = round($totalPrice * ((float) $product->dp_percentage / 100), 2);
        $balanceAmount = $totalPrice - $dpAmount;

        return Inertia::render('Checkout', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_type' => $product->product_type->value,
                'base_price' => (float) $product->base_price,
                'dp_percentage' => (float) $product->dp_percentage,
                'min_lead_time_hours' => $product->min_lead_time_hours,
                'partner_name' => $product->partner?->business_name,
                'partner_city' => $product->partner?->city,
                'media_url' => $product->media->first()?->media_url,
                'tour' => $product->tourPackage ? [
                    'duration_days' => $product->tourPackage->duration_days,
                    'tour_type' => $product->tourPackage->tour_type->label(),
                    'includes_ferry' => (bool) $product->tourPackage->includes_ferry,
                    'min_participants' => $product->tourPackage->min_participants,
                    'max_participants' => $product->tourPackage->max_participants,
                ] : null,
                'vehicle' => $product->vehicle ? [
                    'brand' => $product->vehicle->brand,
                    'model' => $product->vehicle->model,
                    'passenger_capacity' => $product->vehicle->passenger_capacity,
                    'with_driver' => (bool) $product->vehicle->with_driver,
                    'transmission' => $product->vehicle->transmission->label(),
                ] : null,
            ],
            'initialConfig' => [
                'participants' => $participants,
                'rental_days' => $rentalDays,
                'total_price' => $totalPrice,
                'dp_amount' => $dpAmount,
                'balance_amount' => $balanceAmount,
            ],
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request, CreateBookingAction $action): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk menyelesaikan pemesanan.');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'service_start_date' => ['required', 'date'],
            'service_start_time' => ['nullable', 'string'],
            'participant_count' => ['nullable', 'integer', 'min:1'],
            'rental_days' => ['nullable', 'integer', 'min:1'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'dropoff_location' => ['nullable', 'string', 'max:255'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'driver_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        try {
            $booking = $action->execute($user, $product, $validated);

            return redirect()->route('bookings.show', $booking->booking_code)
                ->with('success', 'Pemesanan Anda berhasil diajukan ke mitra vendor. Tunggu konfirmasi maksimal 24 jam.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['service_start_date' => $e->getMessage()]);
        }
    }
}

