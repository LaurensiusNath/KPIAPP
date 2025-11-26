<section class=" dark:bg-gray-900  min-h-screen">
    <div class="max-w-4xl mx-auto space-y-6">
        <header class="space-y-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rencanakan KPI (Bulk)</h1>
            <p class="text-gray-500 dark:text-gray-400">Untuk {{ $user->name }}
                — Periode {{ $activePeriod->year }}/{{ $activePeriod->semester }}</p>
        </header>

        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                {{ session('success') }}</div>
        @endif
        @error('form')
            <div class="p-3 rounded bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">{{ $message }}</div>
        @enderror

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Bobot</div>
                    <div class="text-xl font-semibold">{{ number_format($totalWeight, 2) }}%</div>
                </div>
                <div class="text-sm {{ $totalWeight > 100 ? 'text-red-600' : 'text-gray-500 dark:text-gray-400' }}">
                    Sisa: {{ number_format(max(0, 100 - $totalWeight), 2) }}%
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Judul</th>
                            <th class="px-3 py-2 w-28">Bobot (%)</th>
                            <th class="px-3 py-2">Skala Kriteria (1..5)</th>
                            <th class="px-3 py-2"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $row)
                            <tr class="border-t dark:border-gray-700" wire:key="plan-row-{{ $i }}">
                                <td class="px-3 py-2 align-middle">
                                    {{-- Hidden ID field to track existing KPIs --}}
                                    <input type="hidden" wire:model="items.{{ $i }}.id" />
                                    <input type="text" wire:model.lazy="items.{{ $i }}.title"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 p-2.5" />
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    <input type="number" step="0.01" min="0"
                                        wire:model.lazy="items.{{ $i }}.weight"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 p-2.5" />
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                                        @for ($level = 1; $level <= 5; $level++)
                                            <div>
                                                <label class="block text-xs mb-1">Level {{ $level }}</label>
                                                <input type="text"
                                                    wire:model.lazy="items.{{ $i }}.scale.{{ $level }}"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 p-2" />
                                            </div>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    <div class="flex items-center justify-end h-full">
                                        <button type="button" wire:click="removeRow({{ $i }})"
                                            class="px-3 py-1 text-xs rounded bg-red-600 hover:bg-red-700 text-white">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4">
                <button type="button" wire:click="addRow"
                    class="px-4 py-2 rounded bg-gray-100 dark:bg-gray-700 dark:text-gray-100">+ Tambah Item</button>
                <div class="flex gap-2">
                    <a href="{{ route('tl.kpi.items', ['user' => $user->id]) }}"
                        class="px-4 py-2 rounded border dark:border-gray-700 dark:text-gray-100">Batal</a>
                    <button type="button" wire:click="submit"
                        class="px-4 py-2 rounded bg-primary-700 text-white hover:bg-primary-800"
                        @disabled($totalWeight > 100)">
                        Simpan Semua (Harus 100%)
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
