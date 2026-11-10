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
            'budaya-sejarah' => DestinationCategory::updateOrCreate(
                ['slug' => 'budaya-sejarah'],
                ['name' => ['id' => 'Budaya & Sejarah', 'en' => 'Culture & History'], 'icon' => '🏛️']
            ),
            'alam-viewpoint' => DestinationCategory::updateOrCreate(
                ['slug' => 'alam-viewpoint'],
                ['name' => ['id' => 'Alam & Viewpoint', 'en' => 'Nature & Viewpoints'], 'icon' => '⛰️']
            ),
            'air-terjun-curug' => DestinationCategory::updateOrCreate(
                ['slug' => 'air-terjun-curug'],
                ['name' => ['id' => 'Air Terjun & Curug', 'en' => 'Waterfalls'], 'icon' => '🌊']
            ),
            'pelabuhan-danau' => DestinationCategory::updateOrCreate(
                ['slug' => 'pelabuhan-danau'],
                ['name' => ['id' => 'Pelabuhan & Danau', 'en' => 'Harbors & Lakes'], 'icon' => '🚢']
            ),
        ];

        // 2. Destinations by Cluster
        $destinations = [
            // PARAPAT
            [
                'slug' => 'pelabuhan-penyeberangan-ajibata',
                'name' => ['id' => 'Pelabuhan Penyeberangan Ajibata', 'en' => 'Ajibata Ferry Harbor'],
                'category' => 'pelabuhan-danau',
                'cluster' => DestinationCluster::PARAPAT,
                'description' => [
                    'id' => 'Titik keberangkatan utama ferry penyeberangan kapal roro dan motor menuju Tomok dan Ambarita di Pulau Samosir.',
                    'en' => 'Main departure harbor for Ro-Ro ferries and passenger boats heading to Tomok and Ambarita on Samosir Island.',
                ],
                'ticket_domestic' => 0,
                'ticket_foreign' => 0,
                'operating_hours' => '06:00 - 20:00 WIB',
                'is_featured' => true,
            ],
            [
                'slug' => 'pantai-bebas-parapat',
                'name' => ['id' => 'Pantai Bebas Parapat', 'en' => 'Parapat Free Beach Esplanade'],
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::PARAPAT,
                'description' => [
                    'id' => 'Ruang terbuka publik ikonik di tepi Danau Toba dengan pemandangan terbuka menghadap Pulau Samosir.',
                    'en' => 'Iconic lakeside public promenade on the shores of Lake Toba with panoramic views facing Samosir Island.',
                ],
                'ticket_domestic' => 5000,
                'ticket_foreign' => 10000,
                'operating_hours' => '24 Jam',
                'is_featured' => false,
            ],

            // SAMOSIR EAST
            [
                'slug' => 'desa-wisata-tomok-makam-raja-sidabutar',
                'name' => ['id' => 'Desa Wisata Tomok & Makam Raja Sidabutar', 'en' => 'Tomok Cultural Village & King Sidabutar Tomb'],
                'category' => 'budaya-sejarah',
                'cluster' => DestinationCluster::SAMOSIR_EAST,
                'description' => [
                    'id' => 'Situs bersejarah Batak dengan makam batu monolitik kuno Raja Sidabutar dan pertunjukan boneka Sigale-gale.',
                    'en' => 'Historic Batak cultural site featuring ancient monolithic stone tombs of King Sidabutar and traditional Sigale-gale puppet dances.',
                ],
                'ticket_domestic' => 10000,
                'ticket_foreign' => 20000,
                'operating_hours' => '08:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'slug' => 'batu-kursi-parsidangan-siallagan-ambarita',
                'name' => ['id' => 'Batu Kursi Parsidangan Siallagan (Ambarita)', 'en' => 'Siallagan Stone Court Chairs (Ambarita)'],
                'category' => 'budaya-sejarah',
                'cluster' => DestinationCluster::SAMOSIR_EAST,
                'description' => [
                    'id' => 'Situs persidangan batu zaman megalitikum suku Batak tempat pengadilan dan musyawarah para tetua adat.',
                    'en' => 'Megalithic Batak stone courtroom site where ancient tribal elders and kings held trials and council meetings.',
                ],
                'ticket_domestic' => 10000,
                'ticket_foreign' => 25000,
                'operating_hours' => '08:00 - 17:30 WIB',
                'is_featured' => true,
            ],
            [
                'slug' => 'pesisir-pantai-tuktuk-siadong',
                'name' => ['id' => 'Pesisir Pantai Tuktuk Siadong', 'en' => 'Tuktuk Siadong Peninsula Coast'],
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_EAST,
                'description' => [
                    'id' => 'Semenanjung wisata favorit wisatawan dengan deretan penginapan tepi danau, kafe, dan aktivitas water sport.',
                    'en' => 'Favorite resort peninsula lined with lakeside stays, cozy cafes, and water sport activities.',
                ],
                'ticket_domestic' => 0,
                'ticket_foreign' => 0,
                'operating_hours' => '24 Jam',
                'is_featured' => false,
            ],

            // SAMOSIR WEST
            [
                'slug' => 'bukit-holbung-bukit-teletubbies-samosir',
                'name' => ['id' => 'Bukit Holbung (Bukit Teletubbies Samosir)', 'en' => 'Holbung Hill (Samosir Teletubbies Hill)'],
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_WEST,
                'description' => [
                    'id' => 'Perbukitan savana hijau yang luas dengan panorama menakjubkan 360 derajat menghadap hamparan Danau Toba.',
                    'en' => 'Rolling green savanna hills offering breathtaking 360-degree panoramic views over Lake Toba.',
                ],
                'ticket_domestic' => 10000,
                'ticket_foreign' => 20000,
                'operating_hours' => '06:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'slug' => 'menara-pandang-tele',
                'name' => ['id' => 'Menara Pandang Tele', 'en' => 'Tele Viewing Tower'],
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_WEST,
                'description' => [
                    'id' => 'Titik tertinggi jalur darat penghubung Samosir dengan pulau Sumatra dengan sudut pandang seluruh kaldera.',
                    'en' => 'Highest mainland viewpoint connecting Samosir to Sumatra with sweeping vistas of the entire volcanic caldera.',
                ],
                'ticket_domestic' => 7000,
                'ticket_foreign' => 15000,
                'operating_hours' => '07:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'slug' => 'aek-rangat-pangururan',
                'name' => ['id' => 'Aek Rangat Pangururan', 'en' => 'Aek Rangat Pangururan Hot Springs'],
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::SAMOSIR_WEST,
                'description' => [
                    'id' => 'Pemandian air panas belerang alami di kaki Gunung Pusuk Buhit yang melegenda sebagai asal-muasal suku Batak.',
                    'en' => 'Natural sulfur hot springs at the foot of sacred Mount Pusuk Buhit, legendary birthplace of the Batak people.',
                ],
                'ticket_domestic' => 10000,
                'ticket_foreign' => 15000,
                'operating_hours' => '06:00 - 22:00 WIB',
                'is_featured' => false,
            ],

            // MEREK & TONGGING
            [
                'slug' => 'air-terjun-sipiso-piso',
                'name' => ['id' => 'Air Terjun Sipiso-piso', 'en' => 'Sipiso-piso Waterfall'],
                'category' => 'air-terjun-curug',
                'cluster' => DestinationCluster::MEREK_TONGGING,
                'description' => [
                    'id' => 'Air terjun megah setinggi 120 meter yang mengalir deras dari celah tebing jurang kaldera menuju ke tepian Danau Toba.',
                    'en' => 'Majestic 120-meter waterfall plunging from a subterranean river cave in the caldera cliff towards Lake Toba.',
                ],
                'ticket_domestic' => 10000,
                'ticket_foreign' => 25000,
                'operating_hours' => '07:00 - 18:00 WIB',
                'is_featured' => true,
            ],
            [
                'slug' => 'desa-tongging-danau-utara',
                'name' => ['id' => 'Desa Tongging & Danau Utara', 'en' => 'Tongging Village & Northern Lake'],
                'category' => 'alam-viewpoint',
                'cluster' => DestinationCluster::MEREK_TONGGING,
                'description' => [
                    'id' => 'Desa lembah tepi danau di bawah Air Terjun Sipiso-piso dengan sentra ikan bakar nila segar dan pemandangan tenang.',
                    'en' => 'Serene lakeside valley village below Sipiso-piso Waterfall, famous for fresh grilled tilapia and tranquil waters.',
                ],
                'ticket_domestic' => 0,
                'ticket_foreign' => 0,
                'operating_hours' => '24 Jam',
                'is_featured' => false,
            ],
        ];

        // Approximate GPS coordinates (WGS84) for Google Maps itinerary rendering
        $coordinates = [
            'pelabuhan-penyeberangan-ajibata' => [2.6603, 98.9369],
            'pantai-bebas-parapat' => [2.6662, 98.9354],
            'desa-wisata-tomok-makam-raja-sidabutar' => [2.6539, 98.8559],
            'batu-kursi-parsidangan-siallagan-ambarita' => [2.6797, 98.8236],
            'pesisir-pantai-tuktuk-siadong' => [2.6627, 98.8452],
            'bukit-holbung-bukit-teletubbies-samosir' => [2.5426, 98.6898],
            'menara-pandang-tele' => [2.5487, 98.6006],
            'aek-rangat-pangururan' => [2.6203, 98.6826],
            'air-terjun-sipiso-piso' => [2.9174, 98.5194],
            'desa-tongging-danau-utara' => [2.8967, 98.5303],
        ];

        foreach ($destinations as $d) {
            Destination::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'category_id' => $categories[$d['category']]->id,
                    'name' => $d['name'],
                    'cluster' => $d['cluster'],
                    'description' => $d['description'],
                    'latitude' => $coordinates[$d['slug']][0] ?? null,
                    'longitude' => $coordinates[$d['slug']][1] ?? null,
                    'ticket_price_domestic' => $d['ticket_domestic'],
                    'ticket_price_foreign' => $d['ticket_foreign'],
                    'operating_hours' => $d['operating_hours'],
                    'is_featured' => $d['is_featured'],
                ]
            );
        }
    }
}

