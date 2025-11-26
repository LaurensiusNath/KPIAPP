<section class="dark:bg-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        <header class="space-y-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">KPI Items</h1>
            <p class="text-gray-500 dark:text-gray-400">For {{ $user->name }} ({{ $user->email }})</p>
        </header>

        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                {{ session('success') }}</div>
        @endif
        @error('action')
            <div class="p-3 rounded bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">{{ $message }}</div>
        @enderror

        @if ($activePeriod && !$canEdit)
            <div
                class="p-4 rounded bg-yellow-50 border border-yellow-200 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-200">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <div class="font-semibold mb-1">Outside KPI Creation Period</div>
                        <p class="text-sm">KPI items can only be created or modified during the input window (January
                            1-10 for Semester 1, July 1-10 for Semester 2).</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Active Period</div>
                    <div class="font-semibold">
                        @if ($activePeriod)
                            {{ $activePeriod->year }} / Semester {{ $activePeriod->semester }}
                        @else
                            <span class="text-gray-500">None</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Total Weight</div>
                    <div class="font-semibold">{{ number_format($totalWeight, 2) }}%</div>
                </div>
                <div class="flex items-end justify-end gap-2">
                    @php($hasKpis = $this->kpis && $this->kpis->count() > 0)
                    @if (!$canEdit || !$activePeriod)
                        <button disabled class="px-4 py-2 rounded text-white bg-gray-400 cursor-not-allowed opacity-60"
                            title="{{ !$activePeriod ? 'No active period' : 'Outside creation window' }}">
                            {{ $hasKpis ? 'Edit KPIs' : 'Create KPIs' }}
                        </button>
                    @else
                        <a href="{{ route('tl.kpi.plan', ['user' => $user->id]) }}"
                            class="px-4 py-2 rounded text-white bg-indigo-600 hover:bg-indigo-700">
                            {{ $hasKpis ? 'Edit KPIs' : 'Create KPIs' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->kpis as $kpi)
                        <tr class="border-t dark:border-gray-700" wire:key="kpi-{{ $kpi->id }}">
                            <td class="px-4 py-3">{{ $kpi->title }}</td>
                            <td class="px-4 py-3">{{ number_format($kpi->weight, 2) }}%</td>
                        </tr>
                    @empty
                        <tr class="border-t dark:border-gray-700">
                            <td colspan="2" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No KPI
                                items.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
