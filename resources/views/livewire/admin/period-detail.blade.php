<section class="bg-gray-50 dark:bg-gray-900 p-6 min-h-screen">
    <div class="max-w-4xl mx-auto space-y-6">
        <header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Period Detail</h1>
            <p class="text-gray-600 dark:text-gray-300">Year {{ $period->year }} — Semester {{ $period->semester }}</p>
        </header>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Active</div>
                    <div class="font-semibold">{{ $period->is_active ? 'Yes' : 'No' }}</div>
                </div>
                <div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">KPI Count</div>
                    <div class="font-semibold">{{ $period->kpis_count }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
