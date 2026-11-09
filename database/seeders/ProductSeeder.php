<?php

namespace Database\Seeders;

use App\Enums\ApprovalStatus;
use App\Enums\OwnershipType;
use App\Enums\PricingType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\TourType;
use App\Enums\TransmissionType;
use App\Enums\VehicleType;
use App\Models\Destination;
use App\Models\Partner;
use App\Models\Product;
use App\Models\TourDeparture;
use App\Models\TourItineraryDay;
use App\Models\TourItineraryStop;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $partner = Partner::first();
        $destinations = Destination::all()->keyBy('slug');

        // =========================================================================
        // 1. Tour Package 1: 3D2N Pesona Samosir & Danau Toba
        // =========================================================================
        $tourProduct1 = Product::updateOrCreate(
            ['slug' => '3d2n-pesona-samosir-danau-toba'],
            [
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::TOUR,
                'name' => [
                    'id' => '3D2N Eksplor Eksotik Danau Toba & Budaya Samosir',
                    'en' => '3D2N Exotic Lake Toba & Samosir Cultural Discovery',
                ],
                'short_description' => [
                    'id' => 'Paket komprehensif 3 hari 2 malam menjelajahi Parapat, Desa Tomok, Bukit Holbung, dan Air Panas Pangururan.',
                    'en' => 'Comprehensive 3-day 2-night package exploring Parapat, Tomok Village, Holbung Hill, and Pangururan Hot Springs.',
                ],
                'full_description' => [
                    'id' => 'Nikmati liburan lengkap menjelajah ikon Danau Toba dan Pulau Samosir. Termasuk tiket kapal ferry penyeberangan Danau Toba PP, akomodasi tepi danau di Tuktuk, transportasi privat ber-AC, dan pemandu lokal berlisensi.',
                    'en' => 'Enjoy a complete getaway exploring iconic Lake Toba and Samosir Island. Includes round-trip Lake Toba ferry tickets, lakeside accommodation in Tuktuk, private air-conditioned transport, and a licensed local guide.',
                ],
                'base_price' => 1450000.00,
                'pricing_type' => PricingType::PER_PERSON,
                'dp_percentage' => 30.00,
                'min_lead_time_hours' => 24, // H-1
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::APPROVED,
            ]
        );

        $tour1 = TourPackage::updateOrCreate(
            ['product_id' => $tourProduct1->id],
            [
                'tour_type' => TourType::PRIVATE_TRIP,
                'duration_days' => 3,
                'duration_nights' => 2,
                'min_participants' => 2,
                'max_participants' => 12,
                'inclusions' => [
                    'id' => "- Tiket Ferry Penyeberangan Danau Toba (Ajibata - Tomok PP)\n- Transportasi Privat Mobil / Minibus selama 3 hari\n- Hotel 2 malam di Tuktuk Samosir\n- Tiket masuk seluruh objek wisata\n- Driver merangkap pemandu lokal ramah\n- Air mineral harian",
                    'en' => "- Round-trip Lake Toba Ferry Tickets (Ajibata - Tomok)\n- Private Car / Minibus Transport for 3 days\n- 2 Nights Hotel Stay in Tuktuk Samosir\n- All attraction entrance tickets\n- Friendly local driver-guide\n- Daily bottled mineral water",
                ],
                'exclusions' => [
                    'id' => "- Tiket pesawat ke Kualanamu / Silangit\n- Pengeluaran pribadi & tips driver\n- Makan di luar paket",
                    'en' => "- Flight tickets to Kualanamu / Silangit\n- Personal expenses & driver tips\n- Meals outside the package",
                ],
                'terms_conditions' => [
                    'id' => 'Pemesanan wajib minimal H-1 sebelum keberangkatan. DP awal 30% wajib dilunasi dalam 24 jam setelah ketersediaan disetujui.',
                    'en' => 'Bookings must be made at least D-1 before departure. Initial 30% DP must be paid within 24 hours after availability is confirmed.',
                ],
                'includes_ferry' => true,
            ]
        );

        // Itinerary Day 1
        $day1 = TourItineraryDay::updateOrCreate(
            ['tour_package_id' => $tour1->id, 'day_number' => 1],
            [
                'title' => [
                    'id' => 'Tiba di Parapat & Menyeberang ke Samosir',
                    'en' => 'Arrive in Parapat & Ferry Crossing to Samosir',
                ],
                'description' => [
                    'id' => 'Penjemputan, menuju Parapat, dan menyeberang danau dengan ferry.',
                    'en' => 'Airport pickup, drive to Parapat, and scenic ferry crossing across Lake Toba.',
                ],
            ]
        );

        if ($destinations->has('pelabuhan-penyeberangan-ajibata')) {
            TourItineraryStop::updateOrCreate(
                ['tour_itinerary_day_id' => $day1->id, 'stop_order' => 1],
                [
                    'destination_id' => $destinations['pelabuhan-penyeberangan-ajibata']->id,
                    'title' => [
                        'id' => 'Penyeberangan Ferry Ajibata ke Tomok',
                        'en' => 'Ajibata to Tomok Ferry Crossing',
                    ],
                    'activity_description' => [
                        'id' => 'Menikmati angin sejuk dan panorama danau di atas kapal ferry.',
                        'en' => 'Enjoy cool mountain breezes and volcanic lake panoramas aboard the ferry.',
                    ],
                    'estimated_minutes' => 60,
                ]
            );
        }

        if ($destinations->has('desa-wisata-tomok-makam-raja-sidabutar')) {
            TourItineraryStop::updateOrCreate(
                ['tour_itinerary_day_id' => $day1->id, 'stop_order' => 2],
                [
                    'destination_id' => $destinations['desa-wisata-tomok-makam-raja-sidabutar']->id,
                    'title' => [
                        'id' => 'Eksplor Budaya Batak di Tomok',
                        'en' => 'Explore Batak Heritage in Tomok',
                    ],
                    'activity_description' => [
                        'id' => 'Melihat makam batu kuno Raja Sidabutar dan pertunjukan Sigale-gale.',
                        'en' => 'Visit the ancient stone tomb of King Sidabutar and watch the Sigale-gale performance.',
                    ],
                    'estimated_minutes' => 90,
                ]
            );
        }

        // Itinerary Day 2
        $day2 = TourItineraryDay::updateOrCreate(
            ['tour_package_id' => $tour1->id, 'day_number' => 2],
            [
                'title' => [
                    'id' => 'Puncak Bukit Holbung & Air Panas Pangururan',
                    'en' => 'Holbung Hill Summit & Pangururan Hot Springs',
                ],
                'description' => [
                    'id' => 'Mengeksplorasi panorama alam perbukitan dan relaksasi air panas.',
                    'en' => 'Explore savanna hilltop views and relax in natural thermal springs.',
                ],
            ]
        );

        if ($destinations->has('bukit-holbung-bukit-teletubbies-samosir')) {
            TourItineraryStop::updateOrCreate(
                ['tour_itinerary_day_id' => $day2->id, 'stop_order' => 1],
                [
                    'destination_id' => $destinations['bukit-holbung-bukit-teletubbies-samosir']->id,
                    'title' => [
                        'id' => 'Trekking Ringan Bukit Holbung',
                        'en' => 'Light Trekking at Holbung Hill',
                    ],
                    'activity_description' => [
                        'id' => 'Foto panorama 360 derajat hamparan Danau Toba dari atas padang savana.',
                        'en' => 'Capture 360-degree panoramic photos of Lake Toba from the savanna ridge.',
                    ],
                    'estimated_minutes' => 120,
                ]
            );
        }

        // Departures
        TourDeparture::firstOrCreate(
            ['tour_package_id' => $tour1->id, 'departure_date' => now()->addDays(5)->toDateString()],
            [
                'departure_time' => '08:00:00',
                'max_quota' => 10,
                'booked_quota' => 0,
                'status' => 'OPEN',
            ]
        );

        // =========================================================================
        // 2. Vehicle 1: Toyota Innova Reborn
        // =========================================================================
        $vehProduct1 = Product::updateOrCreate(
            ['slug' => 'toyota-innova-reborn-diesel-plus-driver'],
            [
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::VEHICLE,
                'name' => [
                    'id' => 'Toyota Innova Reborn Diesel + Driver',
                    'en' => 'Toyota Innova Reborn Diesel + Driver',
                ],
                'short_description' => [
                    'id' => 'Medium MPV tangguh, nyaman dan bertenaga untuk melintasi rute perbukitan kaldera Danau Toba.',
                    'en' => 'Reliable, comfortable, and powerful Medium MPV ideal for Lake Toba caldera mountain routes.',
                ],
                'full_description' => [
                    'id' => 'Sewa mobil Innova Reborn Diesel lengkap dengan pengemudi profesional yang hafal rute wisata dan tikungan tajam Danau Toba. Kapasitas hingga 7 orang dengan bagasi luas.',
                    'en' => 'Rent a Toyota Innova Reborn Diesel complete with a professional local driver experienced in Lake Toba tourist routes and mountain turns. Seats up to 7 passengers with spacious luggage room.',
                ],
                'base_price' => 850000.00,
                'pricing_type' => PricingType::PER_VEHICLE,
                'dp_percentage' => 30.00,
                'min_lead_time_hours' => 12, // H-12 Jam
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::APPROVED,
            ]
        );

        Vehicle::firstOrCreate(
            ['product_id' => $vehProduct1->id],
            [
                'vehicle_type' => VehicleType::CAR,
                'brand' => 'Toyota',
                'model' => 'Innova Reborn 2.4 V Diesel',
                'manufacturing_year' => 2022,
                'passenger_capacity' => 7,
                'luggage_capacity' => 4,
                'transmission' => TransmissionType::AUTOMATIC,
                'with_driver' => true,
                'license_plate' => 'BK 1842 TO',
            ]
        );

        // =========================================================================
        // 3. Vehicle 2: Toyota HiAce Commuter 14-Seat
        // =========================================================================
        $vehProduct2 = Product::updateOrCreate(
            ['slug' => 'toyota-hiace-commuter-14-seat-plus-driver'],
            [
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::VEHICLE,
                'name' => [
                    'id' => 'Toyota HiAce Commuter 14-Seat + Driver',
                    'en' => 'Toyota HiAce Commuter 14-Seat + Driver',
                ],
                'short_description' => [
                    'id' => 'Minibus lega dan suspensi empuk, pilihan terbaik untuk rombongan keluarga besar atau tour grup.',
                    'en' => 'Spacious minibus with soft suspension, the best choice for extended family trips or group tours.',
                ],
                'full_description' => [
                    'id' => 'Pilihan armada paling populer untuk rombongan keluarga hingga 14 orang. Kabin tinggi, pendingin udara dingin merata, dan pengemudi berpengalaman.',
                    'en' => 'Most popular fleet choice for family groups up to 14 passengers. High ceiling cabin, evenly distributed AC, and an experienced local driver.',
                ],
                'base_price' => 1350000.00,
                'pricing_type' => PricingType::PER_VEHICLE,
                'dp_percentage' => 30.00,
                'min_lead_time_hours' => 12, // H-12 Jam
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::APPROVED,
            ]
        );

        Vehicle::firstOrCreate(
            ['product_id' => $vehProduct2->id],
            [
                'vehicle_type' => VehicleType::MINIBUS,
                'brand' => 'Toyota',
                'model' => 'HiAce Commuter',
                'manufacturing_year' => 2021,
                'passenger_capacity' => 14,
                'luggage_capacity' => 8,
                'transmission' => TransmissionType::MANUAL,
                'with_driver' => true,
                'license_plate' => 'BK 7109 SM',
            ]
        );
    }
}

