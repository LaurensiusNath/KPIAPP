<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Admin - HRD</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitoring performa dan penilaian KPI
                        karyawan</p>
                </div>
            </div>

            <!-- Active Period Info -->
            @if ($activePeriod)
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Periode Aktif</p>
                                <h3 class="text-2xl font-bold mt-1">Semester {{ $activePeriod->semester }} -
                                    {{ $activePeriod->year }}</h3>
                            </div>
                            <div>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">Tidak ada periode aktif. Silakan aktifkan periode
                                terlebih dahulu.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Appraisal Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Pending Team Leader -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Team Leader</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $appraisalStatus['pending_teamleader'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Menunggu Penilaian TL
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pending HRD -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending HRD</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $appraisalStatus['pending_hrd'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Menunggu Review HRD
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Finalized -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Finalized</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $appraisalStatus['finalized'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Selesai Dinilai
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Performance Chart -->
            @if ($activePeriod)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tren Performa KPI Bulanan
                            per Divisi</h3>
                        <div class="relative" style="height: 400px;">
                            <canvas id="divisionMonthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Monthly Evaluation Status -->
            @if ($activePeriod && count($evaluationStatus) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Penilaian KPI Bulan
                            Ini</h3>
                        <div class="space-y-6">
                            @foreach ($evaluationStatus as $status)
                                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $status['division_name'] }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Leader:
                                                {{ $status['leader_name'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $status['evaluated_staff'] }}/{{ $status['total_staff'] }} Staff</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $status['percentage'] }}% Selesai</p>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                            style="width: {{ $status['percentage'] }}%"></div>
                                    </div>
                                    <div class="flex justify-between mt-2 text-xs">
                                        <span class="text-green-600 dark:text-green-400">✓
                                            {{ $status['evaluated_staff'] }} dinilai</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $status['pending_staff'] }}
                                            pending</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Performers -->
                @if ($activePeriod && count($topPerformers) > 0)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏆 Top Performer Bulan
                                Ini</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">Rank</th>
                                            <th scope="col" class="px-4 py-3">Nama</th>
                                            <th scope="col" class="px-4 py-3">Divisi</th>
                                            <th scope="col" class="px-4 py-3 text-center">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topPerformers as $index => $performer)
                                            <tr
                                                class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                <td class="px-4 py-3">
                                                    @if ($index === 0)
                                                        <span class="text-2xl">🥇</span>
                                                    @elseif($index === 1)
                                                        <span class="text-2xl">🥈</span>
                                                    @else
                                                        <span class="text-2xl">🥉</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                    {{ $performer['name'] }}
                                                </td>
                                                <td class="px-4 py-3">{{ $performer['division'] }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ number_format($performer['score'], 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-1 gap-3">
                            <a href="{{ route('admin.users.index') }}"
                                class="inline-flex items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                                Kelola User
                            </a>
                            <a href="{{ route('admin.divisions.index') }}"
                                class="inline-flex items-center px-4 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Kelola Divisi
                            </a>
                            <a href="{{ route('admin.periods.index') }}"
                                class="inline-flex items-center px-4 py-3 bg-purple-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Kelola Periode
                            </a>
                            <a href="{{ route('admin.appraisals.divisions.index') }}"
                                class="inline-flex items-center px-4 py-3 bg-orange-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Laporan Appraisal
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Division Summary Table -->
            @if ($activePeriod && count($divisionSummary) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Summary Divisi</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Nama Divisi</th>
                                        <th scope="col" class="px-4 py-3">Leader</th>
                                        <th scope="col" class="px-4 py-3 text-center">Jumlah Staff</th>
                                        <th scope="col" class="px-4 py-3 text-center">Rata-rata Bulan Ini</th>
                                        <th scope="col" class="px-4 py-3 text-center">Appraisal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($divisionSummary as $summary)
                                        <tr
                                            class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                {{ $summary['division_name'] }}
                                            </td>
                                            <td class="px-4 py-3">{{ $summary['leader_name'] }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $summary['staff_count'] }} orang
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($summary['monthly_average'] !== null)
                                                    <span
                                                        class="font-semibold text-gray-900 dark:text-white">{{ number_format($summary['monthly_average'], 2) }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex justify-center gap-2">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        {{ $summary['appraisal_finalized'] }} selesai
                                                    </span>
                                                    @if ($summary['appraisal_pending'] > 0)
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ $summary['appraisal_pending'] }} pending
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if ($activePeriod && count($monthlyPerformance) > 0)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4"></script>
            <script>
                window.addEventListener('load', function() {
                    const ctx = document.getElementById('divisionMonthlyChart');
                    if (!ctx) return;

                    const performanceData = @json($monthlyPerformance);

                    // Generate colors for each division
                    const colors = [
                        'rgb(59, 130, 246)', // blue
                        'rgb(16, 185, 129)', // green
                        'rgb(245, 158, 11)', // amber
                        'rgb(239, 68, 68)', // red
                        'rgb(139, 92, 246)', // purple
                        'rgb(236, 72, 153)', // pink
                    ];

                    const datasets = performanceData.map((division, index) => {
                        const color = colors[index % colors.length];
                        return {
                            label: division.division_name,
                            data: division.data.map(d => d.average),
                            borderColor: color,
                            backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                            tension: 0.3,
                            fill: false,
                            spanGaps: false
                        };
                    });

                    const labels = performanceData[0]?.data.map(d => d.label) || [];

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
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
                });
            </script>
        @endpush
    @endif
</div>
