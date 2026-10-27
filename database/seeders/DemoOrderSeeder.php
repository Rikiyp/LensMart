<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayoutStatus;
use App\Enums\ReviewReportStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReport;
use App\Models\Store;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\Support\DemoImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoOrderSeeder extends Seeder
{
    /** @var array<string, Order> */
    private array $orders = [];

    /** @var array<string, Review> */
    private array $reviews = [];

    public function run(): void
    {
        /** @var array<string, Payment> $payments */
        $payments = [];

        foreach ($this->payments() as $spec) {
            $payment = new Payment([
                'code' => $spec['code'],
                'customer_id' => $this->customer($spec['customer'])->id,
                'amount' => 0,
                'method' => $spec['method'],
                'status' => $spec['status'],
                'gateway' => 'simulated',
                'gateway_reference' => $spec['reference'] ?? null,
                'paid_at' => $spec['paid_at'] ?? null,
                'expires_at' => $spec['expires_at'],
            ]);

            $this->stamp($payment, $spec['created_at']);

            $amount = 0;

            foreach ($spec['orders'] as $orderSpec) {
                $order = $this->createOrder($payment, $orderSpec);
                $this->orders[$orderSpec['key']] = $order;
                $amount += $order->total;
            }

            $payment->update(['amount' => $amount]);
            $payments[$spec['key']] = $payment;
        }

        $this->createPayouts();
        $this->createReviews();
        $this->createReports();
        $this->createWishlistsAndCart();
        $this->createNotifications($payments);
    }

    private function createOrder(Payment $payment, array $spec): Order
    {
        $customer = $this->customer($spec['customer']);
        $store = Store::query()->where('name', $spec['store'])->firstOrFail();
        $address = $customer->addresses()->where('is_default', true)->first()
            ?? $customer->addresses()->firstOrFail();
        $shipping = config('lensmart.shipping_methods.'.$spec['shipping']);

        $lines = [];
        $subtotal = 0;

        foreach ($spec['items'] as [$productName, $quantity]) {
            $product = Product::query()->where('name', $productName)->firstOrFail();
            $lineTotal = $product->price * $quantity;
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $lineTotal,
            ];
        }

        $commission = (int) round($subtotal * (float) config('lensmart.commission_rate'));
        $shippingCost = (int) $shipping['cost'];

        $order = new Order([
            'order_number' => $this->nextOrderNumber($spec['created_at']),
            'payment_id' => $payment->id,
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'status' => $spec['status'],
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone,
            'shipping_address' => $address->full_address,
            'shipping_method' => $spec['shipping'],
            'shipping_cost' => $shippingCost,
            'subtotal' => $subtotal,
            'commission_amount' => $commission,
            'total' => $subtotal + $shippingCost,
            'tracking_number' => $spec['tracking'] ?? null,
            'note' => $spec['note'] ?? null,
            'completed_at' => $spec['completed_at'] ?? null,
        ]);

        $this->stamp($order, $spec['created_at']);

        foreach ($lines as $line) {
            $item = $order->items()->make($line);
            $this->stamp($item, $spec['created_at']);
        }

        $this->createHistories($order, $spec, $customer, $store);

        return $order;
    }

    private function createHistories(Order $order, array $spec, User $customer, Store $store): void
    {
        $createdAt = $spec['created_at'];
        $status = $spec['status'];

        $steps = [
            [OrderStatus::WaitingConfirmation, $createdAt, $customer->id, 'Pesanan dibuat, menunggu konfirmasi toko.'],
        ];

        if (! in_array($status, [OrderStatus::WaitingConfirmation, OrderStatus::Cancelled], true)) {
            $steps[] = [OrderStatus::Confirmed, $createdAt->copy()->addHours(6), $store->user_id, 'Pesanan dikonfirmasi oleh toko.'];
        }

        if (in_array($status, [OrderStatus::Packed, OrderStatus::Shipped, OrderStatus::Completed], true)) {
            $steps[] = [OrderStatus::Packed, $createdAt->copy()->addDay(), $store->user_id, 'Pesanan sedang dikemas.'];
        }

        if (in_array($status, [OrderStatus::Shipped, OrderStatus::Completed], true)) {
            $steps[] = [
                OrderStatus::Shipped,
                $createdAt->copy()->addDays(2),
                $store->user_id,
                'Pesanan diserahkan ke kurir'.(isset($spec['tracking']) ? ' dengan resi '.$spec['tracking'] : '').'.',
            ];
        }

        if ($status === OrderStatus::Completed && isset($spec['completed_at'])) {
            $steps[] = [OrderStatus::Completed, $spec['completed_at'], $customer->id, 'Pesanan diterima oleh pelanggan.'];
        }

        if ($status === OrderStatus::Cancelled) {
            $steps[] = [
                OrderStatus::Cancelled,
                $spec['cancelled_at'],
                null,
                $spec['cancel_note'] ?? 'Pesanan dibatalkan.',
            ];
        }

        foreach ($steps as [$stepStatus, $timestamp, $changedBy, $note]) {
            $history = new OrderStatusHistory([
                'order_id' => $order->id,
                'status' => $stepStatus,
                'note' => $note,
                'changed_by' => $changedBy,
            ]);

            $this->stamp($history, $timestamp);
        }
    }

    private function createPayouts(): void
    {
        $payouts = [
            ['order' => 'o1', 'status' => PayoutStatus::Paid, 'paid_at' => now()->subDays(61)->setTime(14, 0)],
            ['order' => 'o2', 'status' => PayoutStatus::Pending, 'paid_at' => null],
            ['order' => 'o3', 'status' => PayoutStatus::Paid, 'paid_at' => now()->subDays(31)->setTime(10, 0)],
            ['order' => 'o4', 'status' => PayoutStatus::Pending, 'paid_at' => null],
            ['order' => 'o7', 'status' => PayoutStatus::Pending, 'paid_at' => null],
        ];

        foreach ($payouts as $spec) {
            $order = $this->orders[$spec['order']];
            $createdAt = ($order->completed_at ?? $order->created_at)->copy()->addHour();

            $payout = new Payout([
                'store_id' => $order->store_id,
                'order_id' => $order->id,
                'amount' => max(0, $order->subtotal - $order->commission_amount),
                'status' => $spec['status'],
                'paid_at' => $spec['paid_at'],
            ]);

            $this->stamp($payout, $createdAt);
        }
    }

    private function createReviews(): void
    {
        $reviews = [
            [
                'key' => 'r1',
                'order' => 'o1',
                'product' => 'Sony Alpha A7 IV',
                'rating' => 5,
                'comment' => 'Kamera datang dengan packing sangat aman. Autofokusnya cepat sekali untuk foto wedding, dan hasil video 4K-nya bersih. Toko juga responsif saat saya tanya soal garansi.',
                'reply' => 'Terima kasih banyak, Kak Rina! Senang mendengar A7 IV-nya membantu pekerjaan. Jangan ragu menghubungi kami jika butuh aksesori tambahan.',
                'replied_at' => now()->subDays(66)->setTime(9, 30),
                'images' => 2,
                'created_at' => now()->subDays(67)->setTime(18, 0),
            ],
            [
                'key' => 'r2',
                'order' => 'o1',
                'product' => 'SanDisk Extreme Pro SDXC 128GB',
                'rating' => 3,
                'comment' => 'Kartu memori bekerja normal dan cepat, tetapi kemasan plastiknya penyok saat tiba. Produk tetap bisa dipakai.',
                'is_hidden' => true,
                'images' => 0,
                'created_at' => now()->subDays(66)->setTime(11, 0),
            ],
            [
                'key' => 'r3',
                'order' => 'o2',
                'product' => 'Rode Wireless GO II',
                'rating' => 4,
                'comment' => 'Suara jernih dan setup-nya mudah. Sayangnya pengiriman terasa agak lambat dibanding estimasi di halaman produk.',
                'images' => 0,
                'created_at' => now()->subDays(67)->setTime(15, 0),
            ],
            [
                'key' => 'r4',
                'order' => 'o3',
                'product' => 'Tamron 28-75mm f/2.8 G2',
                'rating' => 5,
                'comment' => 'Lensa tajam dari f/2.8, autofokus tenang, dan bobotnya pas untuk shooting seharian. Sangat puas beli di sini.',
                'reply' => 'Terima kasih, Kak Dimas! Semoga lensanya makin produktif. Kami siap bantu jika ingin menambah lensa prime.',
                'replied_at' => now()->subDays(36)->setTime(10, 0),
                'images' => 1,
                'created_at' => now()->subDays(36)->setTime(19, 0),
            ],
            [
                'key' => 'r5',
                'order' => 'o3',
                'product' => 'Godox SL60II D',
                'rating' => 4,
                'comment' => 'Lampu terang dan warna netral untuk kebutuhan video. Fan-nya terdengar sedikit, tapi tidak mengganggu rekaman.',
                'images' => 0,
                'created_at' => now()->subDays(35)->setTime(13, 0),
            ],
            [
                'key' => 'r6',
                'order' => 'o4',
                'product' => 'Fujifilm X-T5',
                'rating' => 5,
                'comment' => 'Warna JPEG-nya langsung bagus tanpa edit. Dial fisiknya menyenangkan dipakai dan body terasa solid.',
                'images' => 1,
                'created_at' => now()->subDays(35)->setTime(9, 0),
            ],
            [
                'key' => 'r7',
                'order' => 'o7',
                'product' => 'Canon RF 24-105mm f/4L IS USM',
                'rating' => 5,
                'comment' => 'Rentang focal-nya pas untuk liputan acara. Stabilizer sangat membantu saat merekam handheld, dan pengiriman instan benar-benar cepat.',
                'reply' => 'Terima kasih, Kak Rina! Kami senang lensanya langsung dipakai untuk produksi. Sampai jumpa di pembelian berikutnya.',
                'replied_at' => now()->subDays(4)->setTime(9, 0),
                'images' => 0,
                'created_at' => now()->subDays(4)->setTime(16, 0),
            ],
        ];

        foreach ($reviews as $spec) {
            $order = $this->orders[$spec['order']];
            $product = Product::query()->where('name', $spec['product'])->firstOrFail();

            $review = new Review([
                'product_id' => $product->id,
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'rating' => $spec['rating'],
                'comment' => $spec['comment'],
                'store_reply' => $spec['reply'] ?? null,
                'replied_at' => $spec['replied_at'] ?? null,
                'is_hidden' => $spec['is_hidden'] ?? false,
            ]);

            $this->stamp($review, $spec['created_at']);
            $this->reviews[$spec['key']] = $review;

            for ($imageIndex = 0; $imageIndex < $spec['images']; $imageIndex++) {
                $path = 'demo/reviews/'.$order->order_number.'-'.$product->id.'-'.($imageIndex + 1).'.jpg';

                DemoImages::productImage($path, $product->category->slug, $product->id + $imageIndex);

                $image = new ReviewImage([
                    'review_id' => $review->id,
                    'path' => $path,
                ]);

                $this->stamp($image, $spec['created_at']->copy()->addMinutes(5));
            }
        }
    }

    private function createReports(): void
    {
        $reports = [
            [
                'review' => 'r3',
                'store' => 'GearUp Fotografi',
                'reason' => 'Ulasan menyebut pengiriman lambat, padahal resi menunjukkan paket diterima tepat waktu. Mohon ditinjau agar tidak menyesatkan pembeli lain.',
                'status' => ReviewReportStatus::Open,
                'admin_response' => null,
                'created_at' => now()->subDays(3)->setTime(14, 0),
            ],
            [
                'review' => 'r5',
                'store' => 'Lensa Pro Studio',
                'reason' => 'Ulasan menyinggung produk tidak original tanpa bukti. Kami memiliki faktur resmi untuk unit yang dikirim.',
                'status' => ReviewReportStatus::Resolved,
                'admin_response' => 'Laporan telah ditinjau. Ulasan dipertahankan karena tidak melanggar pedoman komunitas, namun kami sudah menghubungi pelanggan untuk klarifikasi. Partner dapat mengirimkan bukti faktur melalui pusat bantuan.',
                'created_at' => now()->subDays(30)->setTime(11, 0),
            ],
        ];

        foreach ($reports as $spec) {
            $review = $this->reviews[$spec['review']];
            $store = Store::query()->where('name', $spec['store'])->firstOrFail();

            $report = new ReviewReport([
                'review_id' => $review->id,
                'store_id' => $store->id,
                'reason' => $spec['reason'],
                'status' => $spec['status'],
                'admin_response' => $spec['admin_response'],
            ]);

            $this->stamp($report, $spec['created_at']);
        }
    }

    private function createWishlistsAndCart(): void
    {
        $customer = $this->customer('customer@lensmart.test');

        $wishlist = [
            ['Sony Alpha A7C II', 27_499_000],
            ['Canon EOS R5', 42_999_000],
            ['DJI RS 4', 8_999_000],
            ['Fujifilm X-T5', 22_999_000],
        ];

        foreach ($wishlist as [$productName, $priceWhenAdded]) {
            $product = Product::query()->where('name', $productName)->firstOrFail();

            Wishlist::updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $product->id],
                ['price_when_added' => $priceWhenAdded]
            );
        }

        $cart = [
            ['Sony FE 24-70mm f/2.8 GM II', 1],
            ['Rode VideoMic NTG', 1],
            ['Lowepro Tahoe BP 150', 2],
        ];

        foreach ($cart as [$productName, $quantity]) {
            $product = Product::query()->where('name', $productName)->firstOrFail();

            CartItem::updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $product->id],
                ['quantity' => $quantity]
            );
        }
    }

    private function createNotifications(array $payments): void
    {
        $rina = $this->customer('customer@lensmart.test');
        $dimas = $this->customer('dimas@lensmart.test');
        $nadia = $this->customer('nadia@lensmart.test');
        $fajar = $this->customer('fajar@lensmart.test');
        $partner1 = User::query()->where('email', 'partner1@lensmart.test')->firstOrFail();
        $partner2 = User::query()->where('email', 'partner2@lensmart.test')->firstOrFail();
        $partner3 = User::query()->where('email', 'partner3@lensmart.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@lensmart.test')->firstOrFail();

        $this->notify($rina, 'Pembayaran berhasil', 'Pembayaran '.$payments['p4']->code.' sebesar '.format_rupiah($payments['p4']->amount).' telah kami terima.', route('customer.orders.index'), 'check-circle', now()->subDays(11)->setTime(15, 6), true);
        $this->notify($rina, 'Status pesanan diperbarui', 'Pesanan '.$this->orders['o7']->order_number.' kini berstatus Selesai.', route('customer.orders.show', $this->orders['o7']), 'truck', now()->subDays(5)->setTime(12, 5));
        $this->notify($rina, 'Menunggu pembayaran', 'Pesanan '.$this->orders['o10']->order_number.' menunggu pembayaran sebelum dikirim.', route('customer.payments.show', $payments['p6']), 'credit-card', now()->subHours(2));
        $this->notify($nadia, 'Pesanan selesai', 'Pesanan '.$this->orders['o4']->order_number.' telah selesai. Jangan lupa beri ulasan untuk produknya.', route('customer.orders.show', $this->orders['o4']), 'check-circle', now()->subDays(35)->setTime(10, 3), true);
        $this->notify($dimas, 'Status pesanan diperbarui', 'Pesanan '.$this->orders['o8']->order_number.' kini berstatus Dikonfirmasi.', route('customer.orders.show', $this->orders['o8']), 'truck', now()->subDays(10)->setTime(17, 0));
        $this->notify($fajar, 'Pesanan sedang dikirim', 'Pesanan '.$this->orders['o5']->order_number.' sudah diserahkan ke kurir. Lacak resi '.$this->orders['o5']->tracking_number.'.', route('customer.orders.show', $this->orders['o5']), 'truck', now()->subDays(18)->setTime(9, 0));
        $this->notify($partner1, 'Pesanan baru sudah dibayar', 'Pesanan '.$this->orders['o9']->order_number.' siap diproses.', route('partner.orders.show', $this->orders['o9']), 'shopping-bag', now()->subDays(4)->setTime(10, 10));
        $this->notify($partner2, 'Pesanan selesai', 'Pesanan '.$this->orders['o7']->order_number.' telah diterima pelanggan. Pencairan dana sedang diproses.', route('partner.orders.show', $this->orders['o7']), 'wallet', now()->subDays(5)->setTime(12, 10));
        $this->notify($partner3, 'Pesanan baru sudah dibayar', 'Pesanan '.$this->orders['o5']->order_number.' siap diproses.', route('partner.orders.show', $this->orders['o5']), 'shopping-bag', now()->subDays(21)->setTime(8, 15), true);
        $this->notify($admin, 'Laporan ulasan baru', 'Partner GearUp Fotografi melaporkan sebuah ulasan. Tinjau laporan sekarang.', route('admin.reviews.index'), 'flag', now()->subDays(3)->setTime(14, 5));
    }

    /**
     * @param  array<int, array<string, mixed>>  $orders
     * @return array<string, mixed>
     */
    private function nextOrderNumber(Carbon $createdAt): string
    {
        static $counter = 0;
        $counter++;

        return 'LM-'.$createdAt->format('ymd').'-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
    }

    private function customer(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }

    private function stamp(Model $model, Carbon $createdAt, ?Carbon $updatedAt = null): Model
    {
        $model->created_at = $createdAt;
        $model->updated_at = $updatedAt ?? $createdAt;
        $model->save();

        return $model;
    }

    private function notify(
        User $user,
        string $title,
        string $message,
        string $url,
        string $icon,
        Carbon $createdAt,
        bool $read = false,
    ): void {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\PaymentPaid',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'icon' => $icon,
            ], JSON_UNESCAPED_UNICODE),
            'read_at' => $read ? $createdAt : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function payments(): array
    {
        $day = fn (int $daysAgo, int $hour, int $minute = 0): Carbon => now()->subDays($daysAgo)->setTime($hour, $minute);

        return [
            [
                'key' => 'p1',
                'code' => 'PAY-DEMO-0001',
                'customer' => 'customer@lensmart.test',
                'method' => 'transfer_bank',
                'status' => PaymentStatus::Paid,
                'reference' => '8820 1177 2026 0001',
                'created_at' => $day(72, 9, 0),
                'paid_at' => $day(72, 9, 15),
                'expires_at' => $day(71, 9, 0),
                'orders' => [
                    [
                        'key' => 'o1',
                        'customer' => 'customer@lensmart.test',
                        'store' => 'Kamera Nusantara',
                        'status' => OrderStatus::Completed,
                        'created_at' => $day(72, 9, 5),
                        'completed_at' => $day(67, 14, 0),
                        'shipping' => 'jne_reguler',
                        'tracking' => 'JNE-2026-0001',
                        'note' => 'Mohon packing rapi dan sertakan garansi resmi. Terima kasih.',
                        'items' => [
                            ['Sony Alpha A7 IV', 1],
                            ['SanDisk Extreme Pro SDXC 128GB', 1],
                        ],
                    ],
                    [
                        'key' => 'o2',
                        'customer' => 'customer@lensmart.test',
                        'store' => 'GearUp Fotografi',
                        'status' => OrderStatus::Completed,
                        'created_at' => $day(72, 9, 5),
                        'completed_at' => $day(68, 11, 0),
                        'shipping' => 'jnt',
                        'tracking' => 'JNT-2026-0002',
                        'items' => [
                            ['Rode Wireless GO II', 1],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'p2',
                'code' => 'PAY-DEMO-0002',
                'customer' => 'dimas@lensmart.test',
                'method' => 'transfer_bank',
                'status' => PaymentStatus::Paid,
                'reference' => '8820 1177 2026 0002',
                'created_at' => $day(42, 13, 0),
                'paid_at' => $day(42, 13, 20),
                'expires_at' => $day(41, 13, 0),
                'orders' => [
                    [
                        'key' => 'o3',
                        'customer' => 'dimas@lensmart.test',
                        'store' => 'Lensa Pro Studio',
                        'status' => OrderStatus::Completed,
                        'created_at' => $day(42, 13, 5),
                        'completed_at' => $day(37, 16, 0),
                        'shipping' => 'sicepat',
                        'tracking' => 'SC-2026-0003',
                        'items' => [
                            ['Tamron 28-75mm f/2.8 G2', 1],
                            ['Godox SL60II D', 1],
                        ],
                    ],
                    [
                        'key' => 'o4',
                        'customer' => 'nadia@lensmart.test',
                        'store' => 'Kamera Nusantara',
                        'status' => OrderStatus::Completed,
                        'created_at' => $day(42, 13, 5),
                        'completed_at' => $day(36, 10, 0),
                        'shipping' => 'jne_reguler',
                        'tracking' => 'JNE-2026-0004',
                        'items' => [
                            ['Fujifilm X-T5', 1],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'p3',
                'code' => 'PAY-DEMO-0003',
                'customer' => 'fajar@lensmart.test',
                'method' => 'ewallet',
                'status' => PaymentStatus::Paid,
                'reference' => 'EW-2026-0005',
                'created_at' => $day(22, 8, 0),
                'paid_at' => $day(22, 8, 10),
                'expires_at' => $day(21, 8, 0),
                'orders' => [
                    [
                        'key' => 'o5',
                        'customer' => 'fajar@lensmart.test',
                        'store' => 'GearUp Fotografi',
                        'status' => OrderStatus::Shipped,
                        'created_at' => $day(22, 8, 5),
                        'shipping' => 'jne_reguler',
                        'tracking' => 'JNE-2026-0005',
                        'items' => [
                            ['DJI Mini 4 Pro', 1],
                            ['Lowepro Tahoe BP 150', 1],
                        ],
                    ],
                    [
                        'key' => 'o6',
                        'customer' => 'fajar@lensmart.test',
                        'store' => 'Kamera Nusantara',
                        'status' => OrderStatus::Packed,
                        'created_at' => $day(22, 8, 5),
                        'shipping' => 'jnt',
                        'items' => [
                            ['Peak Design Slide Lite', 2],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'p4',
                'code' => 'PAY-DEMO-0004',
                'customer' => 'customer@lensmart.test',
                'method' => 'qris',
                'status' => PaymentStatus::Paid,
                'reference' => 'QRIS-2026-0007',
                'created_at' => $day(11, 15, 0),
                'paid_at' => $day(11, 15, 5),
                'expires_at' => $day(10, 15, 0),
                'orders' => [
                    [
                        'key' => 'o7',
                        'customer' => 'customer@lensmart.test',
                        'store' => 'Lensa Pro Studio',
                        'status' => OrderStatus::Completed,
                        'created_at' => $day(11, 15, 5),
                        'completed_at' => now()->subDays(5)->setTime(12, 0),
                        'shipping' => 'instant',
                        'tracking' => 'INSTANT-2026-0007',
                        'items' => [
                            ['Canon RF 24-105mm f/4L IS USM', 1],
                        ],
                    ],
                    [
                        'key' => 'o8',
                        'customer' => 'dimas@lensmart.test',
                        'store' => 'GearUp Fotografi',
                        'status' => OrderStatus::Confirmed,
                        'created_at' => $day(11, 15, 5),
                        'shipping' => 'jne_reguler',
                        'items' => [
                            ['Fujifilm Instax Mini 12', 1],
                            ['Fujifilm Instax Mini Evo', 1],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'p5',
                'code' => 'PAY-DEMO-0005',
                'customer' => 'nadia@lensmart.test',
                'method' => 'ewallet',
                'status' => PaymentStatus::Paid,
                'reference' => 'EW-2026-0009',
                'created_at' => $day(4, 10, 0),
                'paid_at' => $day(4, 10, 5),
                'expires_at' => $day(3, 10, 0),
                'orders' => [
                    [
                        'key' => 'o9',
                        'customer' => 'nadia@lensmart.test',
                        'store' => 'Kamera Nusantara',
                        'status' => OrderStatus::WaitingConfirmation,
                        'created_at' => $day(4, 10, 5),
                        'shipping' => 'sicepat',
                        'items' => [
                            ['Sony FE 50mm f/1.8', 1],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'p6',
                'code' => 'PAY-DEMO-0006',
                'customer' => 'customer@lensmart.test',
                'method' => null,
                'status' => PaymentStatus::Unpaid,
                'created_at' => now()->subHours(2),
                'paid_at' => null,
                'expires_at' => now()->addHours(22),
                'orders' => [
                    [
                        'key' => 'o10',
                        'customer' => 'customer@lensmart.test',
                        'store' => 'Lensa Pro Studio',
                        'status' => OrderStatus::WaitingConfirmation,
                        'created_at' => now()->subHours(2),
                        'shipping' => 'jne_reguler',
                        'items' => [
                            ['Canon RF 50mm f/1.8 STM', 1],
                            ['Manfrotto Befree Advanced', 1],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'p7',
                'code' => 'PAY-DEMO-0007',
                'customer' => 'customer@lensmart.test',
                'method' => 'qris',
                'status' => PaymentStatus::Expired,
                'reference' => 'QRIS-2026-0011',
                'created_at' => $day(3, 9, 0),
                'paid_at' => null,
                'expires_at' => $day(2, 9, 0),
                'orders' => [
                    [
                        'key' => 'o11',
                        'customer' => 'customer@lensmart.test',
                        'store' => 'GearUp Fotografi',
                        'status' => OrderStatus::Cancelled,
                        'created_at' => $day(3, 9, 5),
                        'cancelled_at' => $day(2, 9, 30),
                        'cancel_note' => 'Pembayaran kedaluwarsa. Stok dikembalikan otomatis.',
                        'shipping' => 'jnt',
                        'items' => [
                            ['DJI Osmo Action 4', 1],
                        ],
                    ],
                ],
            ],
        ];
    }
}
