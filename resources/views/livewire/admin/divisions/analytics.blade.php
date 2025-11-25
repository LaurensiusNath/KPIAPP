<section class=" dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
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
                <p class="text-sm text-gray-500 dark:text-gray-400">Division</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $division->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400">Periode: Semester {{ $period->semester }}
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

        <div class="grid md:grid-cols-3 gap-6">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata KPI Bulan Ini</p>
                <p class="text-4xl font-bold text-primary-700 dark:text-primary-400">
                    {{ $divisionAverage !== null ? number_format($divisionAverage, 2) : '—' }}
                </p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Anggota Dinilai</p>
                <p class="text-4xl font-bold text-gray-900 dark:text-white">
                    {{ collect($userScores)->filter(fn($user) => $user['avg_score'] !== null)->count() }}
                </p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Anggota Divisi</p>
                <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ count($userScores) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Rata-rata KPI</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Periode semester {{ $period->semester }}</p>
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="divisionTrendChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nilai KPI per Anggota</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata skor bulan {{ $selectedMonthLabel }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Rata-rata KPI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($userScores as $user)
                            <tr class="border-t dark:border-gray-700">
                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">{{ $user['name'] }}
                                </td>
                                <td class="px-4 py-2">{{ $user['email'] }}</td>
                                <td class="px-4 py-2 font-semibold">
                                    {{ $user['avg_score'] !== null ? number_format($user['avg_score'], 2) : 'Belum dinilai' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Belum
                                    ada data penilaian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            window.addEventListener('load', () => {
                let divisionChart;
                const monthNumbers = @js($chartMonths);
                const monthLabels = @js($chartLabels);

                const renderDivisionChart = (series = []) => {
                    const ctx = document.getElementById('divisionTrendChart');
                    if (!ctx || typeof Chart === 'undefined') {
                        console.warn('Chart.js belum ter-load atau canvas tidak ditemukan');
                        return;
                    }

                    const monthLookup = series.reduce((bucket, item) => {
                        bucket[item.month] = item.average ?? null;
                        return bucket;
                    }, {});

                    const values = monthNumbers.map((number) => monthLookup[number] ?? null);

                    if (divisionChart) {
                        divisionChart.destroy();
                    }

                    divisionChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthLabels,
                            datasets: [{
                                label: 'Rata-rata KPI',
                                data: values,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37,99,235,0.15)',
                                tension: 0.35,
                                spanGaps: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: 5
                                }
                            }
                        }
                    });
                };

                Livewire.on('division-chart-updated', (payload) => {
                    const series = payload?.data ?? payload;
                    renderDivisionChart(series);
                });

                renderDivisionChart(@js($trendSeries));
            });
        </script>
    @endpush
