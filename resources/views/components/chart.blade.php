<div wire:ignore>
    <canvas id="{{ $id }}" class="max-h-64"></canvas>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('{{ $id }}');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        new Chart(ctx, {
            type: '{{ $type }}',
            data: {
                labels: @json($labels),
                datasets: @json($datasets),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: {{ $legend ? 'true' : 'false' }},
                        position: '{{ $legendPosition ?? 'bottom' }}',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                    },
                },
                scales: {
                    @if ($type === 'line' || $type === 'bar')
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                    if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
                                    return value;
                                }
                            }
                        },
                    @endif
                },
            },
        });
    });
</script>