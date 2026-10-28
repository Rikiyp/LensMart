<div x-data="analyticsChart(@json($endpoint), @json($type))" x-init="init()" class="card p-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-[13px] font-semibold text-ink-950">{{ $title }}</h2>
            <p class="mt-0.5 text-[11px] text-ink-500">{{ $description ?? '' }}</p>
        </div>
        @if ($showControls)
            <div class="flex items-center gap-2">
                <select x-model="period" @change="load()" class="rounded border border-paper-300 bg-paper-50 px-2 py-1 text-[10px] font-semibold text-ink-600">
                    <option value="7">7 hari</option>
                    <option value="30" selected>30 hari</option>
                    <option value="90">90 hari</option>
                </select>
            </div>
        @endif
    </div>

    <div class="relative mt-6">
        <div x-show="loading" class="flex h-48 items-center justify-center">
            <span class="text-[11px] text-ink-400">Memuat grafik...</span>
        </div>
        <canvas x-show="!loading" id="chart-{{ uniqid() }}" class="max-h-64"></canvas>
    </div>
</div>

<script>
function analyticsChart(endpoint, type) {
    return {
        endpoint,
        type,
        period: 30,
        loading: true,
        chart: null,
        chartCanvas: null,

        async init() {
            this.chartCanvas = this.$el.querySelector('canvas');
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                params.append('days', this.period);

                const response = await fetch(`${this.endpoint}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch analytics');
                const data = await response.json();

                this.render(data);
            } catch (error) {
                console.error('Analytics error:', error);
            } finally {
                this.loading = false;
            }
        },

        render(data) {
            const ctx = this.chartCanvas.getContext('2d');

            if (this.chart) {
                this.chart.destroy();
            }

            let chartConfig = {};

            if (this.type === 'revenue') {
                chartConfig = {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Pendapatan',
                                data: data.revenue,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#f59e0b',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Komisi',
                                data: data.commission,
                                borderColor: '#000000',
                                backgroundColor: 'rgba(0, 0, 0, 0.05)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#000000',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }
                        ]
                    }
                };
            } else if (this.type === 'topProducts') {
                chartConfig = {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Terjual',
                                data: data.sales,
                                backgroundColor: '#f59e0b',
                                borderRadius: 4,
                            }
                        ]
                    }
                };
            } else if (this.type === 'orderStatus') {
                chartConfig = {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.counts,
                            backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }]
                    }
                };
            } else if (this.type === 'categories') {
                chartConfig = {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.revenue,
                            backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }]
                    }
                };
            } else if (this.type === 'peakHours') {
                chartConfig = {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Pesanan',
                            data: data.orders,
                            backgroundColor: '#10b981',
                            borderRadius: 4,
                        }]
                    }
                };
            }

            chartConfig.options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: { size: 11 },
                            color: '#6b7280',
                            padding: 12,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';

                                if (context.parsed.y !== undefined) {
                                    const value = context.parsed.y;
                                    if (value >= 1000000) {
                                        label += 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        label += 'Rp ' + (value / 1000).toFixed(1) + 'K';
                                    } else {
                                        label += value;
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    }
                }
            };

            this.chart = new Chart(ctx, chartConfig);
        }
    }
}
</script>