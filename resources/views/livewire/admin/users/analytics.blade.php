<section class=" dark:bg-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        @if (session('info'))
            <div class="p-4 text-sm text-blue-800 bg-blue-50 rounded-lg dark:bg-gray-800 dark:text-blue-300">
                {{ session('info') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 text-sm text-red-800 bg-red-50 rounded-lg dark:bg-gray-800 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @php
            $chartMonths = $period->semester === 1 ? range(1, 6) : range(7, 12);
            $chartLabels = collect($chartMonths)
                ->map(fn($month) => \Carbon\Carbon::create($period->year, $month, 1)->translatedFormat('F'))
                ->values();
        @endphp

        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->division?->name ? 'Divisi: ' . $user->division->name : 'Belum memiliki divisi' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Periode: Semester {{ $period->semester }}
                    {{ $period->year }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bulan {{ $selectedMonthLabel }}</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3">
                <select wire:model.live="month"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full md:w-auto px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach ($monthOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <button type="button" wire:click="downloadReport"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-medium hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:focus:ring-primary-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M6 19h12" />
                    </svg>
                    Download Laporan
                </button>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata KPI Bulan Ini</p>
                <p class="text-4xl font-bold text-primary-700 dark:text-primary-400">
                    {{ $monthlyAverage !== null ? number_format($monthlyAverage, 2) : 'Belum dinilai' }}
                </p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah KPI Dinilai</p>
                <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ count($kpiRows) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Detail KPI Bulan
                    {{ $selectedMonthLabel }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Skor per KPI untuk periode aktif</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Nama KPI</th>
                            <th class="px-4 py-3">Bobot (%)</th>
                            <th class="px-4 py-3">Nilai</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kpiRows as $row)
                            <tr class="border-t dark:border-gray-700">
                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                    {{ $row['title'] ?? '' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ isset($row['weight']) ? number_format($row['weight'], 2) : '—' }}</td>
                                <td class="px-4 py-2 font-semibold">
                                    @if ($row['score'] !== null)
                                        {{ number_format($row['score'], 2) }}
                                        @if ($row['criteria_label'])
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400">({{ $row['criteria_label'] }})</span>
                                        @endif
                                    @else
                                        Belum dinilai
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $row['note'] ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Belum
                                    ada data penilaian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Rata-rata Bulanan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Periode semester {{ $period->semester }}</p>
                </div>
            </div>
            <div class="relative" style="height: 400px;">
                <canvas id="userTrendChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4"></script>
        <script>
            window.addEventListener('load', () => {
                let userChart;
                const monthNumbers = @js($chartMonths);
                const monthLabels = @js($chartLabels);

                const renderUserChart = (series = []) => {
                    const canvas = document.getElementById('userTrendChart');
                    if (!canvas || typeof Chart === 'undefined') {
                        console.warn('Chart.js belum ter-load atau canvas tidak ditemukan');
                        return;
                    }

                    const monthLookup = series.reduce((bucket, item) => {
                        bucket[item.month] = item.average ?? null;
                        return bucket;
                    }, {});

                    const values = monthNumbers.map((number) => monthLookup[number] ?? null);

                    if (userChart) {
                        userChart.destroy();
                    }

                    userChart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: monthLabels,
                            datasets: [{
                                label: 'Rata-rata KPI',
                                data: values,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.3,
                                fill: false,
                                spanGaps: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y.toFixed(2);
                                            } else {
                                                label += 'Belum ada data';
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 5,
                                    ticks: {
                                        stepSize: 1
                                    },
                                    title: {
                                        display: true,
                                        text: 'Nilai KPI'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Bulan'
                                    }
                                }
                            }
                        }
                    });
                };

                Livewire.on('user-chart-updated', payload => {
                    const series = payload?.data ?? payload;
                    renderUserChart(series);
                });

                renderUserChart(@js($trendSeries));
            });
        </script>
    @endpush
</section>
