<section class=" dark:bg-gray-900 min-h-screen">
    <div class="max-w-6xl mx-auto space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Periods</h1>
                <p class="text-gray-500 dark:text-gray-400">Manage KPI periods (semester-based)</p>
            </div>
            <button type="button" wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-primary-700 hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Create Period
            </button>
        </header>

        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Year</th>
                        <th class="px-4 py-3">Semester</th>
                        <th class="px-4 py-3">Active</th>
                        <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->periods as $p)
                        <tr class="border-t dark:border-gray-700" wire:key="period-{{ $p->id }}">
                            <td class="px-4 py-3">{{ $p->year }}</td>
                            <td class="px-4 py-3">{{ $p->semester }}</td>
                            <td class="px-4 py-3">
                                @if ($p->is_active)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded dark:bg-green-900 dark:text-green-200">Active</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded dark:bg-gray-700 dark:text-gray-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="setActive({{ $p->id }})"
                                    class="px-3 py-1 text-xs rounded bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50"
                                    @disabled($p->is_active) wire:loading.attr="disabled" wire:target="setActive">
                                    Set Active
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t dark:border-gray-700">
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No periods
                                found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($this->periods->hasPages())
                <div class="p-4 border-t dark:border-gray-700">
                    {{ $this->periods->links() }}
                </div>
            @endif
        </div>

        {{-- Create Period Modal (Livewire state) --}}
        @if ($showCreateModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-md">
                    <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create Period</h3>
                        <button type="button" wire:click="closeCreateModal"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-white">✕</button>
                    </div>
                    <livewire:admin.periods.create />
                </div>
            </div>
        @endif
    </div>
</section>
