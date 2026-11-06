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
        $destinations = Destination::all()->keyBy('name');

        // =========================================================================
        // 1. Tour Package 1: 3D2N Pesona Samosir & Danau Toba
        // =========================================================================
        $tourProduct1 = Product::firstOrCreate(
            ['slug' => '3d2n-pesona-samosir-danau-toba'],
            [
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::TOUR,
                'name' => '3D2N Eksplor Eksotik Danau Toba & Budaya Samosir',
                'short_description' => 'Paket komprehensif 3 hari 2 malam menjelajahi Parapat, Desa Tomok, Bukit Holbung, dan Air Panas Pangururan.',
                'full_description' => 'Nikmati liburan lengkap menjelajah ikon Danau Toba dan Pulau Samosir. Termasuk tiket kapal ferry penyeberangan Danau Toba PP, akomodasi tepi danau di Tuktuk, transportasi privat ber-AC, dan pemandu lokal berlisensi.',
                'base_price' => 1450000.00,
                'pricing_type' => PricingType::PER_PERSON,
                'dp_percentage' => 30.00,
                'min_lead_time_hours' => 24, // H-1
                'status' => ProductStatus::PUBLISHED,
                'approval_status' => ApprovalStatus::APPROVED,
            ]
        );

        $tour1 = TourPackage::firstOrCreate(
            ['product_id' => $tourProduct1->id],
            [
                'tour_type' => TourType::PRIVATE_TRIP,
                'duration_days' => 3,
                'duration_nights' => 2,
                'min_participants' => 2,
                'max_participants' => 12,
                'inclusions' => "- Tiket Ferry Penyeberangan Danau Toba (Ajibata - Tomok PP)\n- Transportasi Privat Mobil / Minibus selama 3 hari\n- Hotel 2 malam di Tuktuk Samosir\n- Tiket masuk seluruh objek wisata\n- Driver merangkap pemandu lokal ramah\n- Air mineral harian",
                'exclusions' => "- Tiket pesawat ke Kualanamu / Silangit\n- Pengeluaran pribadi & tips driver\n- Makan di luar paket",
                'terms_conditions' => "Pemesanan wajib minimal H-1 sebelum keberangkatan. DP awal 30% wajib dilunasi dalam 24 jam setelah ketersediaan disetujui.",
                'includes_ferry' => true,
            ]
        );

        // Itinerary Day 1
        $day1 = TourItineraryDay::firstOrCreate(
            ['tour_package_id' => $tour1->id, 'day_number' => 1],
            ['title' => 'Tiba di Parapat & Menyeberang ke Samosir', 'description' => 'Penjemputan, menuju Parapat, dan menyeberang danau dengan ferry.']
        );

        if ($destinations->has('Pelabuhan Penyeberangan Ajibata')) {
            TourItineraryStop::firstOrCreate(
                ['tour_itinerary_day_id' => $day1->id, 'stop_order' => 1],
                [
                    'destination_id' => $destinations['Pelabuhan Penyeberangan Ajibata']->id,
                    'title' => 'Penyeberangan Ferry Ajibata ke Tomok',
                    'activity_description' => 'Menikmati angin sejuk dan panorama danau di atas kapal ferry.',
                    'estimated_minutes' => 60,
                ]
            );
        }

        if ($destinations->has('Desa Wisata Tomok & Makam Raja Sidabutar')) {
            TourItineraryStop::firstOrCreate(
                ['tour_itinerary_day_id' => $day1->id, 'stop_order' => 2],
                [
                    'destination_id' => $destinations['Desa Wisata Tomok & Makam Raja Sidabutar']->id,
                    'title' => 'Eksplor Budaya Batak di Tomok',
                    'activity_description' => 'Melihat makam batu kuno Raja Sidabutar dan pertunjukan Sigale-gale.',
                    'estimated_minutes' => 90,
                ]
            );
        }

        // Itinerary Day 2
        $day2 = TourItineraryDay::firstOrCreate(
            ['tour_package_id' => $tour1->id, 'day_number' => 2],
            ['title' => 'Puncak Bukit Holbung & Air Panas Pangururan', 'description' => 'Mengeksplorasi panorama alam perbukitan dan relaksasi air panas.']
        );

        if ($destinations->has('Bukit Holbung (Bukit Teletubbies Samosir)')) {
            TourItineraryStop::firstOrCreate(
                ['tour_itinerary_day_id' => $day2->id, 'stop_order' => 1],
                [
                    'destination_id' => $destinations['Bukit Holbung (Bukit Teletubbies Samosir)']->id,
                    'title' => 'Trekking Ringan Bukit Holbung',
                    'activity_description' => 'Foto panorama 360 derajat hamparan Danau Toba dari atas padang savana.',
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
        $vehProduct1 = Product::firstOrCreate(
            ['slug' => 'toyota-innova-reborn-diesel-plus-driver'],
            [
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::VEHICLE,
                'name' => 'Toyota Innova Reborn Diesel + Driver',
                'short_description' => 'Medium MPV tangguh, nyaman dan bertenaga untuk melintasi rute perbukitan kaldera Danau Toba.',
                'full_description' => 'Sewa mobil Innova Reborn Diesel lengkap dengan pengemudi profesional yang hafal rute wisata dan tikungan tajam Danau Toba. Kapasitas hingga 7 orang dengan bagasi luas.',
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
        $vehProduct2 = Product::firstOrCreate(
            ['slug' => 'toyota-hiace-commuter-14-seat-plus-driver'],
            [
                'partner_id' => $partner->id,
                'ownership_type' => OwnershipType::PARTNER,
                'product_type' => ProductType::VEHICLE,
                'name' => 'Toyota HiAce Commuter 14-Seat + Driver',
                'short_description' => 'Minibus lega dan suspensi empuk, pilihan terbaik untuk rombongan keluarga besar atau tour grup.',
                'full_description' => 'Pilihan armada paling populer untuk rombongan keluarga hingga 14 orang. Kabin tinggi, pendingin udara dingin merata, dan pengemudi berpengalaman.',
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

