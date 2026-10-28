<x-layouts.portal title="Analitik Toko" subtitle="Insight penjualan dan performa produk toko Anda.">
    <div class="grid gap-4 lg:grid-cols-2">
        <x-analytics-chart 
            endpoint="{{ route('api.analytics.top-products') }}" 
            type="topProducts" 
            title="Produk Terlaris" 
            description="10 produk toko dengan penjualan tertinggi." 
            :show-controls="true" 
        />

        <x-analytics-chart 
            endpoint="{{ route('api.analytics.order-status') }}" 
            type="orderStatus" 
            title="Status Pesanan" 
            description="Distribusi status pesanan toko Anda." 
            :show-controls="true" 
        />

        <x-analytics-chart 
            endpoint="{{ route('api.analytics.hours') }}" 
            type="peakHours" 
            title="Jam Sibuk Penjualan" 
            description="Kapan pelanggan paling sering memesan dari toko Anda." 
            :show-controls="true" 
        />
    </div>

    <div class="mt-4">
        <x-analytics-chart 
            endpoint="{{ route('api.analytics.revenue') }}" 
            type="revenue" 
            title="Trend Penjualan" 
            description="Pendapatan dan komisi toko dari waktu ke waktu." 
            :show-controls="false" 
        />
    </div>
</x-layouts.portal>