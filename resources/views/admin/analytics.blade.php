<x-layouts.portal title="Analitik Penjualan" subtitle="Grafik lanjutan dan insight penjualan marketplace.">
    <div class="grid gap-4 lg:grid-cols-2">
        <x-analytics-chart 
            endpoint="{{ route('api.analytics.top-products') }}" 
            type="topProducts" 
            title="Produk Terlaris" 
            description="10 produk dengan penjualan tertinggi." 
            :show-controls="true" 
        />

        <x-analytics-chart 
            endpoint="{{ route('api.analytics.order-status') }}" 
            type="orderStatus" 
            title="Status Pesanan" 
            description="Distribusi status pesanan aktif." 
            :show-controls="true" 
        />

        <x-analytics-chart 
            endpoint="{{ route('api.analytics.categories') }}" 
            type="categories" 
            title="Penjualan per Kategori" 
            description="Revenue breakdown berdasarkan kategori produk." 
            :show-controls="true" 
        />

        <x-analytics-chart 
            endpoint="{{ route('api.analytics.hours') }}" 
            type="peakHours" 
            title="Jam Sibuk Penjualan" 
            description="Peak hours pembuatan pesanan." 
            :show-controls="true" 
        />
    </div>

    <div class="mt-4">
        <x-analytics-chart 
            endpoint="{{ route('api.analytics.revenue') }}" 
            type="revenue" 
            title="Trend Pendapatan" 
            description="Pendapatan dan komisi platform dari waktu ke waktu." 
            :show-controls="false" 
        />
    </div>
</x-layouts.portal>