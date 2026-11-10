<?php

namespace Database\Seeders;

use App\Models\DestinationCluster;
use Illuminate\Database\Seeder;

class DestinationClusterSeeder extends Seeder
{
    public function run(): void
    {
        $clusters = [
            [
                'code' => 'PARAPAT',
                'name' => [
                    'id' => 'Parapat (Gerbang Utama & Pelabuhan)',
                    'en' => 'Parapat (Main Gateway & Harbor)',
                ],
                'description' => [
                    'id' => 'Pintu gerbang utama Danau Toba, pelabuhan kapal ferry Ajibata & Tiga Raja, serta kawasan wisata Pantai Bebas Parapat.',
                    'en' => 'Main gateway of Lake Toba, Ajibata & Tiga Raja ferry harbors, and Pantai Bebas Parapat waterfront.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'SAMOSIR_EAST',
                'name' => [
                    'id' => 'Samosir Timur (Tomok, Ambarita, Tuktuk)',
                    'en' => 'East Samosir (Tomok, Ambarita, Tuktuk)',
                ],
                'description' => [
                    'id' => 'Pusat wisata sejarah dan budaya Batak Toba di Pulau Samosir meliputi Makam Raja Sidabutar, Huta Siallagan, dan semenanjung Tuktuk Siadong.',
                    'en' => 'Cultural and historical center of Batak Toba on Samosir Island including King Sidabutar Tomb, Huta Siallagan, and Tuktuk Siadong peninsula.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'SAMOSIR_WEST',
                'name' => [
                    'id' => 'Samosir Barat (Pangururan & Holbung)',
                    'en' => 'West Samosir (Pangururan & Holbung)',
                ],
                'description' => [
                    'id' => 'Kawasan panorama alam kaldera dan perbukitan hijau meliputi Jembatan Tano Ponggol, Bukit Holbung, Menara Pandang Tele, dan Air Terjun Efrata.',
                    'en' => 'Caldera panoramic highland zone including Tano Ponggol Bridge, Holbung Hill, Tele Observation Tower, and Efrata Waterfall.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'MEREK_TONGGING',
                'name' => [
                    'id' => 'Merek & Tongging (Sipiso-piso & Kaldera Utara)',
                    'en' => 'Merek & Tongging (Sipiso-piso & North Caldera)',
                ],
                'description' => [
                    'id' => 'Sisi utara kaldera Danau Toba di Kabupaten Karo & Dairi meliputi Air Terjun Sipiso-piso, Bukit Gajah Bobok, dan tepian Silalahi.',
                    'en' => 'Northern rim of Lake Toba caldera in Karo & Dairi regencies featuring Sipiso-piso Waterfall, Gajah Bobok Hill, and Silalahi shore.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'MEDAN_HUB',
                'name' => [
                    'id' => 'Medan Hub (Titik Keberangkatan & Bandara)',
                    'en' => 'Medan Hub (Departure Point & Airport)',
                ],
                'description' => [
                    'id' => 'Simpul transportasi utama Sumatera Utara meliputi Bandara Internasional Kualanamu (KNO), Istana Maimun, Masjid Raya, dan kuliner Kota Medan.',
                    'en' => 'Primary transit hub of North Sumatra including Kualanamu International Airport (KNO), Maimun Palace, Grand Mosque, and Medan culinary spots.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'BUKIT_LAWANG',
                'name' => [
                    'id' => 'Bukit Lawang (Ekowisata Taman Nasional Gunung Leuser)',
                    'en' => 'Bukit Lawang (Gunung Leuser National Park Ecotourism)',
                ],
                'description' => [
                    'id' => 'Kawasan konservasi orangutan Sumatera, jungle trekking hutan hujan tropis, dan tubing Sungai Bahorok di Kabupaten Langkat.',
                    'en' => 'Sumatran orangutan conservation zone, tropical rainforest jungle trekking, and Bahorok River tubing in Langkat Regency.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'code' => 'BERASTAGI',
                'name' => [
                    'id' => 'Berastagi & Dataran Tinggi Karo',
                    'en' => 'Berastagi & Karo Highlands',
                ],
                'description' => [
                    'id' => 'Kawasan wisata pegunungan sejuk berlatar Gunung Sibayak dan Gunung Sinabung, Pasar Buah Berastagi, serta Pemandian Air Panas Sidebuk-debuk.',
                    'en' => 'Cool mountain resort highland framed by Mount Sibayak and Mount Sinabung, Berastagi Fruit Market, and Sidebuk-debuk Hot Springs.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'code' => 'TANGKAHAN',
                'name' => [
                    'id' => 'Tangkahan (The Hidden Paradise of Sumatra)',
                    'en' => 'Tangkahan (The Hidden Paradise of Sumatra)',
                ],
                'description' => [
                    'id' => 'Ekowisata gajah Sumatera, sungai jernih pertemuan Sungai Buluh dan Sungai Batang Serangan, serta pemandian air panas alami di tepi hutan.',
                    'en' => 'Sumatran elephant sanctuary ecotourism, crystal-clear river confluence, and natural rainforest hot springs.',
                ],
                'province' => 'Sumatera Utara',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($clusters as $item) {
            DestinationCluster::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}

