<?php

namespace Database\Seeders;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\VerificationStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCompatibility;
use App\Models\ProductImage;
use App\Models\ProductSpec;
use App\Models\SpecAttribute;
use App\Models\Store;
use Database\Seeders\Support\DemoImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::query()->get()->keyBy('name');
        $categories = Category::query()->get()->keyBy('slug');
        $brands = Brand::query()->get()->keyBy('name');
        $attributes = SpecAttribute::query()
            ->get()
            ->keyBy(fn (SpecAttribute $attribute) => $attribute->category_id.'|'.$attribute->name);

        foreach ($this->products() as $index => $data) {
            $store = $stores->get($data['store']);
            $category = $categories->get($data['category']);
            $brand = $brands->get($data['brand']);

            if (! $store || ! $category || ! $brand) {
                continue;
            }

            $verification = $data['verification'] ?? VerificationStatus::Approved;
            $condition = $data['condition'] ?? ProductCondition::New;

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'store_id' => $store->id,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'model_name' => $data['model'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'condition' => $condition,
                    'status' => $data['status'] ?? ($data['stock'] > 0 ? ProductStatus::Active : ProductStatus::OutOfStock),
                    'verification_status' => $verification,
                    'rejection_note' => $data['rejection_note'] ?? null,
                ]
            );

            $product->images()->delete();
            $imageCount = $verification === VerificationStatus::Approved ? 2 : 1;

            for ($imageIndex = 0; $imageIndex < $imageCount; $imageIndex++) {
                $path = 'demo/products/'.$product->slug.($imageIndex === 0 ? '' : '-'.($imageIndex + 1)).'.jpg';

                DemoImages::productImage($path, $category->slug, $index + ($imageIndex * 5));

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'is_primary' => $imageIndex === 0,
                    'sort_order' => $imageIndex,
                ]);
            }

            $product->specs()->delete();

            foreach ($data['specs'] as $name => $value) {
                $attribute = $attributes->get($category->id.'|'.$name);

                if (! $attribute) {
                    $attribute = SpecAttribute::create([
                        'name' => $name,
                        'category_id' => $category->id,
                    ]);

                    $attributes->put($category->id.'|'.$name, $attribute);
                }

                ProductSpec::create([
                    'product_id' => $product->id,
                    'spec_attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }

            $product->compatibilities()->delete();

            foreach ($data['compat'] ?? [] as [$compatibleBrandName, $modelName]) {
                $compatibleBrand = $brands->get($compatibleBrandName);

                if (! $compatibleBrand) {
                    continue;
                }

                ProductCompatibility::create([
                    'product_id' => $product->id,
                    'brand_id' => $compatibleBrand->id,
                    'model_name' => $modelName,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        return [
            // Kamera Nusantara - Jakarta Selatan
            [
                'store' => 'Kamera Nusantara',
                'category' => 'kamera-mirrorless',
                'brand' => 'Sony',
                'name' => 'Sony Alpha A7 IV',
                'model' => 'ILCE-7M4',
                'price' => 30_999_000,
                'stock' => 8,
                'description' => 'Full-frame all-rounder dengan sensor 33 MP, autofokus real-time yang tajam, dan video 4K 60p. Cocok untuk wedding, portrait, dan produksi konten harian.',
                'specs' => [
                    'Resolusi Sensor' => '33 MP',
                    'Tipe Sensor' => 'Full-frame BSI CMOS',
                    'Mount Lensa' => 'Sony E',
                    'ISO Maksimum' => '51200',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '659 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'kamera-mirrorless',
                'brand' => 'Sony',
                'name' => 'Sony Alpha A7C II',
                'model' => 'ILCE-7CM2',
                'price' => 26_499_000,
                'stock' => 6,
                'description' => 'Body ringkas full-frame 33 MP dengan stabilisasi 7 stop dan autofokus AI. Pilihan tepat untuk travel dan street photography.',
                'specs' => [
                    'Resolusi Sensor' => '33 MP',
                    'Tipe Sensor' => 'Full-frame BSI CMOS',
                    'Mount Lensa' => 'Sony E',
                    'ISO Maksimum' => '51200',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '514 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'kamera-mirrorless',
                'brand' => 'Canon',
                'name' => 'Canon EOS R6 Mark II',
                'model' => 'EOS R6 II',
                'price' => 31_999_000,
                'stock' => 5,
                'description' => 'Kecepatan burst 40 fps, autofokus subject detection, dan video 4K 60p. Dirancang untuk aksi, wildlife, dan liputan acara.',
                'specs' => [
                    'Resolusi Sensor' => '24 MP',
                    'Tipe Sensor' => 'Full-frame CMOS',
                    'Mount Lensa' => 'Canon RF',
                    'ISO Maksimum' => '102400',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '670 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'kamera-mirrorless',
                'brand' => 'Nikon',
                'name' => 'Nikon Z6 III',
                'model' => 'Z6III',
                'price' => 33_999_000,
                'stock' => 4,
                'description' => 'Sensor partially stacked 24,5 MP dengan video 6K dan EVF terang. Performa seimbang untuk foto dan video profesional.',
                'specs' => [
                    'Resolusi Sensor' => '24.5 MP',
                    'Tipe Sensor' => 'Full-frame Partially Stacked CMOS',
                    'Mount Lensa' => 'Nikon Z',
                    'ISO Maksimum' => '64000',
                    'Video Maksimum' => '6K 60p',
                    'Berat' => '760 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'kamera-mirrorless',
                'brand' => 'Fujifilm',
                'name' => 'Fujifilm X-T5',
                'model' => 'X-T5',
                'price' => 22_999_000,
                'stock' => 7,
                'description' => 'Sensor APS-C 40 MP dengan warna khas Fujifilm dan dial fisik klasik. Favorit fotografer jalanan dan portrait.',
                'specs' => [
                    'Resolusi Sensor' => '40 MP',
                    'Tipe Sensor' => 'APS-C X-Trans 5 HR',
                    'Mount Lensa' => 'Fujifilm X',
                    'ISO Maksimum' => '12800',
                    'Video Maksimum' => '6.2K 30p',
                    'Berat' => '557 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'lensa',
                'brand' => 'Sony',
                'name' => 'Sony FE 24-70mm f/2.8 GM II',
                'model' => 'SEL2470GM2',
                'price' => 33_999_000,
                'stock' => 3,
                'description' => 'Lensa zoom standar G Master generasi kedua yang ringan dengan ketajaman tinggi di seluruh rentang focal.',
                'specs' => [
                    'Focal Length' => '24-70 mm',
                    'Aperture Maksimum' => 'f/2.8',
                    'Mount' => 'Sony E',
                    'Filter Thread' => '82 mm',
                    'Berat' => '695 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Sony', 'A7C II'],
                ],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'lensa',
                'brand' => 'Sony',
                'name' => 'Sony FE 50mm f/1.8',
                'model' => 'SEL50F18F',
                'price' => 3_199_000,
                'stock' => 12,
                'description' => 'Lensa prime 50 mm ringan dan tajam dengan bokeh lembut, ideal untuk portrait dan low-light.',
                'specs' => [
                    'Focal Length' => '50 mm',
                    'Aperture Maksimum' => 'f/1.8',
                    'Mount' => 'Sony E',
                    'Filter Thread' => '49 mm',
                    'Berat' => '186 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Sony', 'A7C II'],
                    ['Sony', 'ZV-E10 II'],
                ],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'lensa',
                'brand' => 'Sigma',
                'name' => 'Sigma 18-50mm f/2.8 DC DN',
                'model' => '18-50mm F2.8 DC DN',
                'price' => 5_999_000,
                'stock' => 10,
                'description' => 'Zoom standar ringkas untuk mount Sony E dengan aperture konstan f/2.8. Praktis untuk dokumentasi dan vlog.',
                'specs' => [
                    'Focal Length' => '18-50 mm',
                    'Aperture Maksimum' => 'f/2.8',
                    'Mount' => 'Sony E',
                    'Filter Thread' => '55 mm',
                    'Berat' => '290 g',
                ],
                'compat' => [
                    ['Sony', 'A7C II'],
                    ['Sony', 'ZV-E10 II'],
                ],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'aksesori',
                'brand' => 'SanDisk',
                'name' => 'SanDisk Extreme Pro SDXC 128GB',
                'model' => 'SDSDXXY-128G',
                'price' => 1_099_000,
                'stock' => 25,
                'description' => 'Kartu SDXC UHS-I berkecepatan tinggi hingga 200 MB/s untuk merekam video 4K dan burst foto.',
                'specs' => [
                    'Tipe Aksesori' => 'Kartu memori SDXC',
                    'Kompatibilitas' => 'Kamera dengan slot UHS-I',
                    'Berat' => '2 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Fujifilm', 'X-T5'],
                ],
            ],
            [
                'store' => 'Kamera Nusantara',
                'category' => 'aksesori',
                'brand' => 'Peak Design',
                'name' => 'Peak Design Slide Lite',
                'model' => 'SL-BK-3',
                'price' => 749_000,
                'stock' => 18,
                'description' => 'Tali kamera yang bisa dipakai sebagai sling, neck, atau shoulder strap dengan sistem anchor quick-release.',
                'specs' => [
                    'Tipe Aksesori' => 'Tali kamera',
                    'Kompatibilitas' => 'Kamera mirrorless dan DSLR',
                    'Berat' => '170 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Nikon', 'Z6 III'],
                    ['Fujifilm', 'X-T5'],
                ],
            ],

            // Lensa Pro Studio - Bandung
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'kamera-mirrorless',
                'brand' => 'Canon',
                'name' => 'Canon EOS R5',
                'model' => 'EOS R5',
                'price' => 42_999_000,
                'stock' => 3,
                'description' => 'Sensor full-frame 45 MP, video 8K, dan stabilisasi in-body. Flagship serbaguna untuk studio dan produksi komersial.',
                'specs' => [
                    'Resolusi Sensor' => '45 MP',
                    'Tipe Sensor' => 'Full-frame CMOS',
                    'Mount Lensa' => 'Canon RF',
                    'ISO Maksimum' => '51200',
                    'Video Maksimum' => '8K 30p',
                    'Berat' => '738 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'kamera-mirrorless',
                'brand' => 'Panasonic',
                'name' => 'Panasonic Lumix GH7',
                'model' => 'DC-GH7',
                'price' => 32_999_000,
                'stock' => 4,
                'description' => 'Kamera micro four thirds dengan video internal ProRes dan stabilisasi kuat. Andalan videografer dan filmmaker.',
                'specs' => [
                    'Resolusi Sensor' => '25 MP',
                    'Tipe Sensor' => 'Micro Four Thirds CMOS',
                    'Mount Lensa' => 'Micro Four Thirds',
                    'ISO Maksimum' => '25600',
                    'Video Maksimum' => '5.7K 60p',
                    'Berat' => '805 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'kamera-mirrorless',
                'brand' => 'Sony',
                'name' => 'Sony Alpha A7 IV (Bekas Mulus)',
                'model' => 'ILCE-7M4',
                'price' => 24_499_000,
                'stock' => 1,
                'condition' => ProductCondition::LikeNew,
                'description' => 'Unit bekas mulus body only, kelengkapan lengkap dan sudah dicek fungsi. Kesempatan memiliki full-frame 33 MP dengan harga lebih ringan.',
                'specs' => [
                    'Resolusi Sensor' => '33 MP',
                    'Tipe Sensor' => 'Full-frame BSI CMOS',
                    'Mount Lensa' => 'Sony E',
                    'ISO Maksimum' => '51200',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '659 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'lensa',
                'brand' => 'Canon',
                'name' => 'Canon RF 24-105mm f/4L IS USM',
                'model' => 'RF24-105L',
                'price' => 18_499_000,
                'stock' => 5,
                'description' => 'Lensa zoom serbaguna L-series dengan rentang focal fleksibel dan stabilisasi optik. Cocok untuk reportase dan perjalanan.',
                'specs' => [
                    'Focal Length' => '24-105 mm',
                    'Aperture Maksimum' => 'f/4',
                    'Mount' => 'Canon RF',
                    'Filter Thread' => '77 mm',
                    'Berat' => '700 g',
                ],
                'compat' => [
                    ['Canon', 'EOS R5'],
                    ['Canon', 'EOS R6 II'],
                    ['Canon', 'EOS R50'],
                ],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'lensa',
                'brand' => 'Canon',
                'name' => 'Canon RF 50mm f/1.8 STM',
                'model' => 'RF50F18',
                'price' => 2_999_000,
                'stock' => 14,
                'description' => 'Lensa prime ringan dengan bokeh indah dan autofokus cepat untuk portrait maupun daily carry.',
                'specs' => [
                    'Focal Length' => '50 mm',
                    'Aperture Maksimum' => 'f/1.8',
                    'Mount' => 'Canon RF',
                    'Filter Thread' => '43 mm',
                    'Berat' => '160 g',
                ],
                'compat' => [
                    ['Canon', 'EOS R5'],
                    ['Canon', 'EOS R6 II'],
                    ['Canon', 'EOS R50'],
                ],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'lensa',
                'brand' => 'Tamron',
                'name' => 'Tamron 28-75mm f/2.8 G2',
                'model' => 'A063',
                'price' => 13_999_000,
                'stock' => 6,
                'description' => 'Lensa zoom f/2.8 dengan autofokus linear yang cepat dan ketajaman merata, alternatif ringan untuk mount Sony E.',
                'specs' => [
                    'Focal Length' => '28-75 mm',
                    'Aperture Maksimum' => 'f/2.8',
                    'Mount' => 'Sony E',
                    'Filter Thread' => '67 mm',
                    'Berat' => '540 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Sony', 'A7C II'],
                ],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'lighting-studio',
                'brand' => 'Godox',
                'name' => 'Godox SL60II D',
                'model' => 'SL60II-D',
                'price' => 2_199_000,
                'stock' => 9,
                'description' => 'Lampu LED daylight 60 W dengan CRI tinggi dan mount Bowens. Praktis untuk studio kecil dan konten video.',
                'specs' => [
                    'Daya' => '60 W',
                    'Suhu Warna' => '5600 K',
                    'Tipe Mount' => 'Bowens',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'lighting-studio',
                'brand' => 'Godox',
                'name' => 'Godox AD200 Pro',
                'model' => 'AD200Pro',
                'price' => 5_499_000,
                'stock' => 6,
                'description' => 'Flash strobe portabel 200 Ws dengan dua kepala lampu, cocok untuk foto outdoor dan setup studio ringan.',
                'specs' => [
                    'Daya' => '200 W',
                    'Suhu Warna' => '5600 K',
                    'Tipe Mount' => 'Bowens',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Nikon', 'Z6 III'],
                ],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'tripod-stabilizer',
                'brand' => 'Manfrotto',
                'name' => 'Manfrotto Befree Advanced',
                'model' => 'MKBFRTA4BK',
                'price' => 3_799_000,
                'stock' => 8,
                'description' => 'Tripod travel aluminium ringan dengan kepala ball dan pengunci cepat, mendukung beban hingga 8 kg.',
                'specs' => [
                    'Kapasitas Beban' => '8 kg',
                    'Tinggi Maksimum' => '165 cm',
                    'Berat' => '1590 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Nikon', 'Z6 III'],
                ],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'tripod-stabilizer',
                'brand' => 'DJI',
                'name' => 'DJI RS 4',
                'model' => 'RS4',
                'price' => 8_499_000,
                'stock' => 5,
                'description' => 'Gimbal 3-axis untuk kamera mirrorless dengan kapasitas 3 kg dan balancing otomatis. Stabilkan footage sinematik.',
                'specs' => [
                    'Kapasitas Beban' => '3 kg',
                    'Tinggi Maksimum' => '24 cm (mode ringkas)',
                    'Berat' => '1060 g',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R6 II'],
                    ['Panasonic', 'Lumix GH7'],
                ],
            ],

            // GearUp Fotografi - Surabaya
            [
                'store' => 'GearUp Fotografi',
                'category' => 'kamera-instan',
                'brand' => 'Fujifilm',
                'name' => 'Fujifilm Instax Mini 12',
                'model' => 'Instax Mini 12',
                'price' => 1_199_000,
                'stock' => 20,
                'description' => 'Kamera instan dengan cetak langsung dan mode close-up. Warna ceria untuk dokumentasi sehari-hari.',
                'specs' => [
                    'Tipe Film' => 'Instax Mini ISO 800',
                    'Jarak Fokus' => '30-50 cm (close-up)',
                    'Dimensi' => '104 x 66 x 122 mm',
                ],
                'compat' => [],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'kamera-instan',
                'brand' => 'Fujifilm',
                'name' => 'Fujifilm Instax Mini Evo',
                'model' => 'Instax Mini Evo',
                'price' => 2_499_000,
                'stock' => 9,
                'description' => 'Kamera instan hybrid dengan layar LCD dan 10 efek lensa untuk hasil kreatif langsung cetak.',
                'specs' => [
                    'Tipe Film' => 'Instax Mini ISO 800',
                    'Jarak Fokus' => '28 mm (setara 35 mm)',
                    'Dimensi' => '122 x 87 x 36 mm',
                ],
                'compat' => [],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'kamera-action',
                'brand' => 'DJI',
                'name' => 'DJI Osmo Action 4',
                'model' => 'Osmo Action 4',
                'price' => 4_999_000,
                'stock' => 7,
                'description' => 'Action cam tahan air 18 m dengan sensor 1/1.3 inci dan video 4K 120p. Siap untuk aktivitas ekstrem.',
                'specs' => [
                    'Resolusi Video' => '4K 120p',
                    'Ketahanan Air' => '18 m',
                    'Berat' => '145 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'kamera-mirrorless',
                'brand' => 'Canon',
                'name' => 'Canon EOS R6 Mark II (Bekas Mulus)',
                'model' => 'EOS R6 II',
                'price' => 24_999_000,
                'stock' => 1,
                'condition' => ProductCondition::LikeNew,
                'description' => 'Unit bekas mulus dengan performa autofokus dan video yang masih ganas. Sudah melalui pemeriksaan fungsi dan sensor.',
                'specs' => [
                    'Resolusi Sensor' => '24 MP',
                    'Tipe Sensor' => 'Full-frame CMOS',
                    'Mount Lensa' => 'Canon RF',
                    'ISO Maksimum' => '102400',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '670 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'drone',
                'brand' => 'DJI',
                'name' => 'DJI Mini 4 Pro',
                'model' => 'Mini 4 Pro',
                'price' => 10_999_000,
                'stock' => 4,
                'description' => 'Drone ringan 249 g dengan video 4K 100p, sensor omnidirectional, dan waktu terbang hingga 34 menit.',
                'specs' => [
                    'Resolusi Video' => '4K 100p',
                    'Waktu Terbang' => '34 menit',
                    'Jarak Transmisi' => '20 km',
                    'Berat' => '249 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'audio',
                'brand' => 'Rode',
                'name' => 'Rode Wireless GO II',
                'model' => 'WIGOII',
                'price' => 4_499_000,
                'stock' => 6,
                'description' => 'Sistem mikrofon nirkabel dual-channel dengan perekam internal dan jangkauan luas. Praktis untuk wawancara dan vlog.',
                'specs' => [
                    'Tipe Mikrofon' => 'Lavalier nirkabel',
                    'Pola Polar' => 'Omnidirectional',
                    'Koneksi' => 'USB-C / 3.5 mm',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Panasonic', 'Lumix GH7'],
                ],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'audio',
                'brand' => 'Rode',
                'name' => 'Rode VideoMic NTG',
                'model' => 'VideoMic NTG',
                'price' => 3_899_000,
                'stock' => 5,
                'description' => 'Mikrofon shotgun on-camera dengan output USB-C dan 3.5 mm. Suara fokus untuk video dan film.',
                'specs' => [
                    'Tipe Mikrofon' => 'Shotgun',
                    'Pola Polar' => 'Supercardioid',
                    'Koneksi' => 'USB-C / 3.5 mm',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Panasonic', 'Lumix GH7'],
                ],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'tas-case',
                'brand' => 'Lowepro',
                'name' => 'Lowepro ProTactic BP 350 AW II',
                'model' => 'LP36771',
                'price' => 2_899_000,
                'stock' => 8,
                'description' => 'Tas punggung modular dengan akses empat arah, kompartemen kamera luas, dan cover hujan.',
                'specs' => [
                    'Kapasitas' => '16 L',
                    'Dimensi' => '30 x 18 x 47 cm',
                    'Bahan' => 'Nylon 1680D',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Nikon', 'Z6 III'],
                ],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'tas-case',
                'brand' => 'Lowepro',
                'name' => 'Lowepro Tahoe BP 150',
                'model' => 'LP36892',
                'price' => 899_000,
                'stock' => 15,
                'description' => 'Tas punggung harian ringan untuk kamera DSLR/mirrorless dan laptop, dengan kompartemen serbaguna.',
                'specs' => [
                    'Kapasitas' => '15 L',
                    'Dimensi' => '27 x 16 x 45 cm',
                    'Bahan' => 'Polyester',
                ],
                'compat' => [
                    ['Sony', 'A7 IV'],
                    ['Canon', 'EOS R5'],
                    ['Fujifilm', 'X-T5'],
                ],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'tripod-stabilizer',
                'brand' => 'Manfrotto',
                'name' => 'Manfrotto Pixi Mini',
                'model' => 'MTPIXI-B',
                'price' => 899_000,
                'stock' => 12,
                'description' => 'Tripod mini serbaguna dengan kepala ball, cocok untuk kamera ringkas, action cam, dan smartphone.',
                'specs' => [
                    'Kapasitas Beban' => '1 kg',
                    'Tinggi Maksimum' => '13.5 cm',
                    'Berat' => '190 g',
                ],
                'compat' => [
                    ['Sony', 'ZV-E10 II'],
                    ['Canon', 'EOS R50'],
                    ['Fujifilm', 'X-T5'],
                ],
            ],

            // Produk untuk antrean moderasi admin
            [
                'store' => 'Kamera Nusantara',
                'category' => 'kamera-mirrorless',
                'brand' => 'Sony',
                'name' => 'Sony Alpha ZV-E10 II',
                'model' => 'ZV-E10 II',
                'price' => 15_999_000,
                'stock' => 5,
                'verification' => VerificationStatus::Pending,
                'description' => 'Kamera APS-C untuk content creator dengan video 4K 60p, layar lipat, dan autofokus pelacakan wajah.',
                'specs' => [
                    'Resolusi Sensor' => '26 MP',
                    'Tipe Sensor' => 'APS-C Exmor R CMOS',
                    'Mount Lensa' => 'Sony E',
                    'ISO Maksimum' => '51200',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '377 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'Lensa Pro Studio',
                'category' => 'kamera-mirrorless',
                'brand' => 'Canon',
                'name' => 'Canon EOS R50',
                'model' => 'EOS R50',
                'price' => 9_999_000,
                'stock' => 0,
                'verification' => VerificationStatus::Pending,
                'description' => 'Kamera mirrorless APS-C ringan dengan video 4K dan antarmuka ramah pemula. Sempurna untuk mulai membuat konten.',
                'specs' => [
                    'Resolusi Sensor' => '24 MP',
                    'Tipe Sensor' => 'APS-C CMOS',
                    'Mount Lensa' => 'Canon RF',
                    'ISO Maksimum' => '25600',
                    'Video Maksimum' => '4K 30p',
                    'Berat' => '375 g',
                ],
                'compat' => [],
            ],
            [
                'store' => 'GearUp Fotografi',
                'category' => 'kamera-mirrorless',
                'brand' => 'Nikon',
                'name' => 'Nikon Zf',
                'model' => 'Zf',
                'price' => 29_999_000,
                'stock' => 3,
                'verification' => VerificationStatus::Rejected,
                'rejection_note' => 'Foto produk tidak menampilkan unit yang dijual dan deskripsi belum mencantumkan kelengkapan paket.',
                'description' => 'Body retro full-frame dengan kontrol dial dan performa modern. Desain klasik untuk pengguna yang ingin tampil beda.',
                'specs' => [
                    'Resolusi Sensor' => '24.5 MP',
                    'Tipe Sensor' => 'Full-frame BSI CMOS',
                    'Mount Lensa' => 'Nikon Z',
                    'ISO Maksimum' => '64000',
                    'Video Maksimum' => '4K 60p',
                    'Berat' => '710 g',
                ],
                'compat' => [],
            ],
        ];
    }
}
