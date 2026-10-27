<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\SpecAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Kamera Mirrorless',
            'Kamera DSLR',
            'Kamera Instan',
            'Kamera Action',
            'Lensa',
            'Aksesori',
            'Lighting & Studio',
            'Tripod & Stabilizer',
            'Tas & Case',
            'Drone',
            'Audio',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        $brands = [
            'Sony', 'Canon', 'Nikon', 'Fujifilm', 'Panasonic', 'OM System',
            'Sigma', 'Tamron', 'DJI', 'Godox', 'Manfrotto', 'Peak Design',
            'SanDisk', 'Lowepro', 'Rode',
        ];

        foreach ($brands as $name) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        $attributes = [
            'kamera-mirrorless' => [
                ['Resolusi Sensor', 'MP'],
                ['Tipe Sensor', null],
                ['Mount Lensa', null],
                ['ISO Maksimum', null],
                ['Video Maksimum', null],
                ['Berat', 'g'],
            ],
            'kamera-dslr' => [
                ['Resolusi Sensor', 'MP'],
                ['Tipe Sensor', null],
                ['Mount Lensa', null],
                ['ISO Maksimum', null],
                ['Video Maksimum', null],
                ['Berat', 'g'],
            ],
            'kamera-instan' => [
                ['Tipe Film', null],
                ['Jarak Fokus', 'mm'],
                ['Dimensi', null],
            ],
            'kamera-action' => [
                ['Resolusi Video', null],
                ['Ketahanan Air', 'm'],
                ['Berat', 'g'],
            ],
            'lensa' => [
                ['Focal Length', 'mm'],
                ['Aperture Maksimum', null],
                ['Mount', null],
                ['Filter Thread', 'mm'],
                ['Berat', 'g'],
            ],
            'aksesori' => [
                ['Tipe Aksesori', null],
                ['Kompatibilitas', null],
                ['Berat', 'g'],
            ],
            'lighting-studio' => [
                ['Daya', 'W'],
                ['Suhu Warna', 'K'],
                ['Tipe Mount', null],
            ],
            'tripod-stabilizer' => [
                ['Kapasitas Beban', 'kg'],
                ['Tinggi Maksimum', 'cm'],
                ['Berat', 'g'],
            ],
            'tas-case' => [
                ['Kapasitas', 'L'],
                ['Dimensi', null],
                ['Bahan', null],
            ],
            'drone' => [
                ['Resolusi Video', null],
                ['Waktu Terbang', 'menit'],
                ['Jarak Transmisi', 'km'],
                ['Berat', 'g'],
            ],
            'audio' => [
                ['Tipe Mikrofon', null],
                ['Pola Polar', null],
                ['Koneksi', null],
            ],
        ];

        foreach ($attributes as $categorySlug => $items) {
            $category = Category::where('slug', $categorySlug)->first();

            foreach ($items as [$name, $unit]) {
                SpecAttribute::updateOrCreate(
                    ['name' => $name, 'category_id' => $category?->id],
                    ['unit' => $unit]
                );
            }
        }
    }
}
