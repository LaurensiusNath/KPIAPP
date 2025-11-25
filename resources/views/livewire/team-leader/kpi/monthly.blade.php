<section class="bg-gray-50 dark:bg-gray-900 p-6 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Penilaian KPI Bulanan</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ $user->name }} — Bulan
                        {{ \Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F') }}</p>
                </div>
                @if ($readonly)
                    <span
                        class="text-xs px-2.5 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Readonly</span>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 w-1/3">Item KPI</th>
                            <th class="px-4 py-3 w-24">Skor</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kpis as $kpi)
                            <tr class="border-t dark:border-gray-700">
                                <td class="px-4 py-3 align-top">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $kpi->title }}</div>
                                    @php($scale = $scaleLegend[$kpi->id] ?? [])
                                    @if ($scale && count(array_filter($scale, fn($text) => $text !== '')))
                                        <ul class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                            @foreach ($scale as $score => $description)
                                                @if ($description !== '')
                                                    <li>
                                                        <span
                                                            class="font-semibold text-gray-700 dark:text-gray-200">Skor
                                                            {{ $score }}:</span>
                                                        <span>{{ $description }}</span>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <select @disabled($readonly) wire:model="scores.{{ $kpi->id }}"
                                        class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                        <option value="">Pilih</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <textarea @disabled($readonly) wire:model.lazy="notes.{{ $kpi->id }}" rows="2"
                                        class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" wire:click="submit" @disabled($readonly)
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-700 rounded-lg hover:bg-primary-800 disabled:opacity-60">Submit
                    Penilaian</button>
            </div>
        </div>
    </div>
</section>
