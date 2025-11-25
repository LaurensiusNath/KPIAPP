<section class="bg-gray-50 dark:bg-gray-900 p-6 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Riwayat Penilaian — {{ $user->name }}</h2>
            <p class="text-gray-500 dark:text-gray-400">Periode aktif:
                {{ $activePeriod?->year }}/{{ $activePeriod?->semester }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($byMonth as $m => $data)
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Bulan
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</h3>
                        @if (!is_null($data['average']))
                            <span
                                class="text-xs px-2.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Rata-rata:
                                {{ $data['average'] }}</span>
                        @else
                            <span
                                class="text-xs px-2.5 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Belum
                                dinilai</span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-2">Item KPI</th>
                                    <th class="px-3 py-2 w-20">Skor</th>
                                    <th class="px-3 py-2 w-28">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['items'] as $item)
                                    <tr class="border-t dark:border-gray-700">
                                        <td class="px-3 py-2">{{ $item['title'] }}</td>
                                        <td class="px-3 py-2">{{ $item['score'] ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            @if ($item['submitted'])
                                                <span
                                                    class="text-xs px-2.5 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Submitted</span>
                                            @else
                                                <span
                                                    class="text-xs px-2.5 py-0.5 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
