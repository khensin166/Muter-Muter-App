<?php

namespace Database\Seeders;

use App\Enums\DestinationCluster;
use App\Models\Destination;
use App\Models\DestinationCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categories = [
            'budaya-sejarah' => DestinationCategory::firstOrCreate(
                ['slug' => 'budaya-sejarah'],
                ['name' => 'Budaya & Sejarah', 'icon' => '🏛️']
            ),
            'alam-viewpoint' => DestinationCategory::firstOrCreate(
                ['slug' => 'alam-viewpoint'],
                ['name' => 'Alam & Viewpoint', 'icon' => '⛰️']
            ),
            'air-terjun-curug' => DestinationCategory::firstOrCreate(
                ['slug' => 'air-terjun-curug'],
                ['name' => 'Air Terjun & Curug', 'icon' => '🌊']
            ),
            'pelabuhan-danau' => DestinationCategory::firstOrCreate(
                ['slug' => 'pelabuhan-danau'],
                ['name' => 'Pelabuhan & Danau', 'icon' => '🚢']
            ),
        ];

        // 2. Destinations by Cluster
        $destinations = [
            // PARAPAT
            [
                'name' => 'Pelabuhan Penyeberangan Ajibata',
                'category' => 'pelabuhan-danau',
                'cluster' => DestinationCluster::PARAPAT,
                'description' => 'Titik keberangkatan utama ferry penyeberangan kapal roro dan motor menuju Tomok dan Ambarita di Pulau Samosir.',
                'ticket_domestic' => 0,
                'ticket_foreign' => 0,
                'operating_hours' => '06:00 - 20:00 WIB',
                'is_featured' => true,
            ],
            [
                'name' => 'Pantai Bebas Parapat',
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::PARAPAT,
                'description' => 'Ruang terbuka publik ikonik di tepi Danau Toba dengan pemandangan terbuka menghadap Pulau Samosir.',
                'ticket_domestic' => 5000,
                'ticket_foreign' => 10000,
                'operating_hours' => '24 Jam',
                'is_featured' => false,
            ],

            // SAMOSIR EAST
            [
                'name' => 'Desa Wisata Tomok & Makam Raja Sidabutar',
                'category' => 'budaya-sejarah',
                'cluster' => DestinationCluster::SAMOSIR_EAST,
                'description' => 'Situs bersejarah Batak dengan makam batu monolitik kuno Raja Sidabutar dan pertunjukan boneka Sigale-gale.',
                'ticket_domestic' => 10000,
                'ticket_foreign' => 20000,
                'operating_hours' => '08:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'name' => 'Batu Kursi Parsidangan Siallagan (Ambarita)',
                'category' => 'budaya-sejarah',
                'cluster' => DestinationCluster::SAMOSIR_EAST,
                'description' => 'Situs persidangan batu zaman megalitikum suku Batak tempat pengadilan dan musyawarah para tetua adat.',
                'ticket_domestic' => 10000,
                'ticket_foreign' => 25000,
                'operating_hours' => '08:00 - 17:30 WIB',
                'is_featured' => true,
            ],
            [
                'name' => 'Pesisir Pantai Tuktuk Siadong',
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_EAST,
                'description' => 'Semenanjung wisata favorit wisatawan dengan deretan penginapan tepi danau, kafe, dan aktivitas water sport.',
                'ticket_domestic' => 0,
                'ticket_foreign' => 0,
                'operating_hours' => '24 Jam',
                'is_featured' => false,
            ],

            // SAMOSIR WEST
            [
                'name' => 'Bukit Holbung (Bukit Teletubbies Samosir)',
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_WEST,
                'description' => 'Perbukitan savana hijau yang luas dengan panorama menakjubkan 360 derajat menghadap hamparan Danau Toba.',
                'ticket_domestic' => 10000,
                'ticket_foreign' => 20000,
                'operating_hours' => '06:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'name' => 'Menara Pandang Tele',
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_WEST,
                'description' => 'Titik tertinggi jalur darat penghubung Samosir dengan pulau Sumatra dengan sudut pandang seluruh kaldera.',
                'ticket_domestic' => 7000,
                'ticket_foreign' => 15000,
                'operating_hours' => '07:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'name' => 'Aek Rangat Pangururan',
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_WEST,
                'description' => 'Pemandian air panas belerang alami di kaki Gunung Pusuk Buhit yang melegenda sebagai asal-muasal suku Batak.',
                'ticket_domestic' => 10000,
                'ticket_foreign' => 15000,
                'operating_hours' => '06:00 - 22:00 WIB',
                'is_featured' => false,
            ],

            // MEREK & TONGGING
            [
                'name' => 'Air Terjun Sipiso-piso',
                'category' => 'air-terjun-curug',
                'cluster' => DestinationCluster::MEREK_TONGGING,
                'description' => 'Air terjun megah setinggi 120 meter yang mengalir deras dari celah tebing jurang kaldera menuju ke tepian Danau Toba.',
                'ticket_domestic' => 10000,
                'ticket_foreign' => 25000,
                'operating_hours' => '07:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'name' => 'Desa Tongging & Danau Utara',
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::MEREK_TONGGING,
                'description' => 'Desa lembah tepi danau di bawah Air Terjun Sipiso-piso dengan sentra ikan bakar nila segar dan pemandangan tenang.',
                'ticket_domestic' => 0,
                'ticket_foreign' => 0,
                'operating_hours' => '24 Jam',
                'is_featured' => false,
            ],
        ];

        foreach ($destinations as $d) {
            Destination::firstOrCreate(
                ['slug' => Str::slug($d['name'])],
                [
                    'category_id' => $categories[$d['category']]->id,
                    'name' => $d['name'],
                    'cluster' => $d['cluster'],
                    'description' => $d['description'],
                    'ticket_price_domestic' => $d['ticket_domestic'],
                    'ticket_price_foreign' => $d['ticket_foreign'],
                    'operating_hours' => $d['operating_hours'],
                    'is_featured' => $d['is_featured'],
                ]
            );
        }
    }
}

