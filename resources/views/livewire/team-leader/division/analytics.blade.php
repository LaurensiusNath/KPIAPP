<section class="dark:bg-gray-900 min-h-screen">
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

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Team Leader</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $division->name }}</p>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v12m0 0l-4-4m4 4l4-4M6 19h12" />
                            </svg>
                            Download Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Period Info -->
        @if ($period)
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Periode Aktif</p>
                            <h3 class="text-2xl font-bold mt-1">Semester {{ $period->semester }} -
                                {{ $period->year }}</h3>
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
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Staff -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-blue-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Staff</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $evaluationStatus['total_staff'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluated This Month -->
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
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dinilai
                                {{ $selectedMonthLabel }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $evaluationStatus['evaluated_staff'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-green-600 h-2.5 rounded-full"
                                style="width: {{ $evaluationStatus['percentage'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $evaluationStatus['percentage'] ?? 0 }}%
                            Complete</p>
                    </div>
                </div>
            </div>

            <!-- Division Average -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-purple-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Divisi</p>
                            <p class="text-2xl font-bold text-primary-700 dark:text-primary-400">
                                {{ $divisionStats['division_average'] !== null ? number_format($divisionStats['division_average'], 2) : '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total KPIs -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-amber-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total KPI</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $divisionStats['total_kpis'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appraisal Status Cards -->
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Appraisal</h2>
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
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Appraisal Pending</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $appraisalStatus['pending_teamleader'] ?? 0 }}</p>
                        </div>
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
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Menunggu HRD</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $appraisalStatus['pending_hrd'] ?? 0 }}</p>
                        </div>
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
                                {{ $appraisalStatus['finalized'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Performance Chart -->
        @if (count($monthlyPerformance) > 0)
            @php
                $chartMonths = $period->semester === 1 ? range(1, 6) : range(7, 12);
                $chartLabels = collect($chartMonths)
                    ->map(fn($month) => \Carbon\Carbon::create($period->year, $month, 1)->translatedFormat('F'))
                    ->values();
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tren Performa KPI Tim</h3>
                    <div class="relative" style="height: 400px;">
                        <canvas id="teamMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Performers -->
            @if (count($topPerformers) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏆 Top Performer
                            {{ $selectedMonthLabel }}
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Rank</th>
                                        <th scope="col" class="px-4 py-3">Nama</th>
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
                        <a href="{{ route('tl.members') }}"
                            class="inline-flex items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            Kelola Anggota Tim
                        </a>
                        <a href="{{ route('tl.kpi.members') }}"
                            class="inline-flex items-center px-4 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
                            </svg>
                            Penilaian KPI Bulanan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Summary Table -->
        @if (count($staffSummary) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Summary Anggota Tim -
                        {{ $selectedMonthLabel }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Nama</th>
                                    <th scope="col" class="px-4 py-3">Email</th>
                                    <th scope="col" class="px-4 py-3 text-center">Nilai {{ $selectedMonthLabel }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-center">Status Penilaian</th>
                                    <th scope="col" class="px-4 py-3 text-center">Appraisal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($staffSummary as $staff)
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $staff['name'] }}
                                        </td>
                                        <td class="px-4 py-3">{{ $staff['email'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($staff['monthly_average'] !== null)
                                                <span
                                                    class="font-semibold text-gray-900 dark:text-white">{{ number_format($staff['monthly_average'], 2) }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($staff['is_evaluated'])
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Sudah Dinilai
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($staff['appraisal_status'] === 'Finalized')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Finalized
                                                </span>
                                            @elseif($staff['appraisal_status'] === 'Pending HRD')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Pending HRD
                                                </span>
                                            @elseif($staff['appraisal_status'] === 'Pending TL')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending TL
                                                </span>
                                            @else
                                                <span
                                                    class="text-gray-400 text-xs">{{ $staff['appraisal_status'] }}</span>
                                            @endif
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

    @if (count($monthlyPerformance) > 0)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
            <script>
                let teamMonthlyChartInstance = null;

                const initTeamMonthlyChart = () => {
                    const ctx = document.getElementById('teamMonthlyChart');
                    if (!ctx || typeof Chart === 'undefined') {
                        console.warn('Chart.js belum ter-load atau canvas tidak ditemukan');
                        return;
                    }

                    // Destroy existing chart instance if exists
                    if (teamMonthlyChartInstance) {
                        teamMonthlyChartInstance.destroy();
                    }

                    const performanceData = @json($monthlyPerformance);
                    const chartMonths = @json($chartMonths);
                    const chartLabels = @json($chartLabels);

                    console.log('Initializing chart with data:', performanceData);

                    const colors = [
                        'rgb(59, 130, 246)', // blue
                        'rgb(16, 185, 129)', // green
                        'rgb(245, 158, 11)', // amber
                        'rgb(239, 68, 68)', // red
                        'rgb(139, 92, 246)', // purple
                        'rgb(236, 72, 153)', // pink
                        'rgb(20, 184, 166)', // teal
                        'rgb(251, 146, 60)', // orange
                    ];

                    const datasets = performanceData.map((member, index) => {
                        const color = colors[index % colors.length];
                        return {
                            label: member.user_name,
                            data: member.data.map(d => d.average),
                            borderColor: color,
                            backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                            tension: 0.3,
                            fill: false,
                            spanGaps: true
                        };
                    });

                    teamMonthlyChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartLabels,
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
                                            size: 11
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
                                    suggestedMax: 5,
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

                    console.log('Chart initialized successfully');
                };

                // Initial load
                window.addEventListener('load', () => {
                    console.log('Page loaded, initializing chart...');
                    initTeamMonthlyChart();
                });

                // Listen to Livewire event for chart updates
                document.addEventListener('livewire:init', () => {
                    Livewire.on('team-chart-updated', (payload) => {
                        console.log('Chart update event received:', payload);
                        setTimeout(initTeamMonthlyChart, 100);
                    });
                });
            </script>
        @endpush
    @endif
</section>
