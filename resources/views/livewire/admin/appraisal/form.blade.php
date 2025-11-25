<section class=" dark:bg-gray-900 min-h-screen">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Appraisal 6 Bulanan - HRD</h1>
                <p class="text-gray-500 dark:text-gray-400">User: {{ $user->name }} | Periode: Semester
                    {{ $period->semester }} {{ $period->year }}</p>
            </div>
            <span
                class="text-xs px-3 py-1 rounded-full
                @class([
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' =>
                        $statusBadge === 'Waiting for Team Leader',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' =>
                        $statusBadge === 'Waiting for HRD',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' =>
                        $statusBadge === 'Finalized',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' => !in_array(
                        $statusBadge,
                        ['Waiting for Team Leader', 'Waiting for HRD', 'Finalized']),
                ])">{{ $statusBadge }}</span>
        </div>

        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">{{ session('error') }}</div>
        @endif

        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Rangkuman KPI Semester</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">KPI</th>
                            @foreach ($summary['months'] as $m)
                                <th class="px-4 py-3">Bulan {{ $m }}</th>
                            @endforeach
                            <th class="px-4 py-3">Rata-rata</th>
                            <th class="px-4 py-3">Weighted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['kpis'] as $row)
                            <tr class="border-t dark:border-gray-700">
                                <td class="px-4 py-2">{{ $row['title'] }}<div class="text-xs text-gray-500">Bobot:
                                        {{ $row['weight'] }}</div>
                                </td>
                                @foreach ($summary['months'] as $m)
                                    <td class="px-4 py-2">{{ $row['monthly_scores'][$m] ?? '-' }}</td>
                                @endforeach
                                <td class="px-4 py-2 font-medium">{{ $row['average_score'] }}</td>
                                <td class="px-4 py-2 font-medium">{{ $row['weighted_average'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm">
                        <tr class="font-semibold">
                            <td class="px-4 py-2">Total</td>
                            @foreach ($summary['months'] as $m)
                                <td class="px-4 py-2"></td>
                            @endforeach
                            <td class="px-4 py-2">{{ $summary['total_average'] }}</td>
                            <td class="px-4 py-2">{{ $summary['total_weighted_average'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nilai Akhir (Weighted)</p>
                <p class="text-3xl font-bold text-primary-700 dark:text-primary-400">
                    {{ $appraisal?->final_score ?? $summary['total_weighted_average'] }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow md:col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Komentar HRD</label>
                <textarea wire:model.defer="comment_hrd" @disabled($readonly) rows="4"
                    class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                @error('comment_hrd')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-4 text-right">
                    <button type="button" wire:click="submit" @disabled($readonly)
                        class="px-5 py-2.5 text-sm font-medium text-white bg-primary-700 rounded-lg hover:bg-primary-800 disabled:opacity-60">Submit
                        HRD</button>
                </div>
            </div>
        </div>
    </div>
</section>
