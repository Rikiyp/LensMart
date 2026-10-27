<?php

namespace Database\Seeders;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\Support\DemoImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Andi Pratama',
                'email' => 'partner1@lensmart.test',
                'phone' => '081200000011',
                'store' => [
                    'name' => 'Kamera Nusantara',
                    'city' => 'Jakarta Selatan',
                    'address' => 'Jl. Radio Dalam Raya No. 18, Gandaria Utara',
                    'description' => 'Spesialis kamera mirrorless dan lensa premium dengan garansi resmi. Melayani pengiriman aman ke seluruh Indonesia.',
                    'bank' => ['BCA', '1234567890', 'Andi Pratama'],
                    'status' => StoreStatus::Verified,
                    'logo' => 'demo/stores/kamera-nusantara.jpg',
                    'initials' => 'KN',
                ],
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'partner2@lensmart.test',
                'phone' => '081200000012',
                'store' => [
                    'name' => 'Lensa Pro Studio',
                    'city' => 'Bandung',
                    'address' => 'Jl. Buah Batu No. 77, Lengkong',
                    'description' => 'Studio dan toko lensa profesional. Menyediakan lensa prime, zoom, serta perlengkapan lighting untuk kreator.',
                    'bank' => ['Mandiri', '9876543210', 'Siti Rahmawati'],
                    'status' => StoreStatus::Verified,
                    'logo' => 'demo/stores/lensa-pro-studio.jpg',
                    'initials' => 'LP',
                ],
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'partner3@lensmart.test',
                'phone' => '081200000013',
                'store' => [
                    'name' => 'GearUp Fotografi',
                    'city' => 'Surabaya',
                    'address' => 'Jl. Darmo Permai Selatan No. 5, Pradahkalikendal',
                    'description' => 'Pusat gear fotografi dan videografi: tripod, gimbal, tas, audio, dan aksesori harian dengan harga bersaing.',
                    'bank' => ['BNI', '5556667778', 'Budi Santoso'],
                    'status' => StoreStatus::Verified,
                    'logo' => 'demo/stores/gearup-fotografi.jpg',
                    'initials' => 'GU',
                ],
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'partner4@lensmart.test',
                'phone' => '081200000014',
                'store' => [
                    'name' => 'Snap House',
                    'city' => 'Yogyakarta',
                    'address' => 'Jl. Kaliurang KM 5, Sleman',
                    'description' => 'Toko kamera instan dan aksesori kreatif untuk generasi muda.',
                    'bank' => ['BRI', '1122334455', 'Dewi Lestari'],
                    'status' => StoreStatus::Pending,
                    'logo' => 'demo/stores/snap-house.jpg',
                    'initials' => 'SH',
                ],
            ],
            [
                'name' => 'Rizky Maulana',
                'email' => 'partner5@lensmart.test',
                'phone' => '081200000015',
                'store' => [
                    'name' => 'Pixel Corner',
                    'city' => 'Medan',
                    'address' => 'Jl. Gatot Subroto No. 101, Medan Baru',
                    'description' => 'Penyedia kamera bekas berkualitas dengan pemeriksaan menyeluruh.',
                    'bank' => ['BCA', '8899001122', 'Rizky Maulana'],
                    'status' => StoreStatus::Rejected,
                    'logo' => 'demo/stores/pixel-corner.jpg',
                    'initials' => 'PC',
                ],
            ],
        ];

        foreach ($partners as $index => $partner) {
            $user = User::updateOrCreate(
                ['email' => $partner['email']],
                [
                    'name' => $partner['name'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Partner,
                    'phone' => $partner['phone'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            DemoImages::storeLogo($partner['store']['logo'], $partner['store']['initials'], $index);

            Store::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $partner['store']['name'],
                    'slug' => Str::slug($partner['store']['name']).'-'.($index + 1),
                    'description' => $partner['store']['description'],
                    'address' => $partner['store']['address'],
                    'city' => $partner['store']['city'],
                    'phone' => $partner['phone'],
                    'logo' => $partner['store']['logo'],
                    'status' => $partner['store']['status'],
                    'rejection_note' => $partner['store']['status'] === StoreStatus::Rejected
                        ? 'Dokumen legalitas usaha tidak terbaca. Mohon unggah ulang dokumen yang jelas.'
                        : null,
                    'bank_name' => $partner['store']['bank'][0],
                    'account_number' => $partner['store']['bank'][1],
                    'account_holder' => $partner['store']['bank'][2],
                ]
            );
        }
    }
}
