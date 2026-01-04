<section class="dark:bg-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        <header class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Anggota Divisi
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400">Kelola item KPI untuk anggota tim</p>
                </div>
                <div class="w-full md:w-80">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" id="search"
                            class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Cari nama atau email...">
                    </div>
                </div>
            </div>
        </header>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Status KPI Items</th>
                        <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->filteredMembers as $m)
                        <tr class="border-t dark:border-gray-700">
                            <td class="px-4 py-3">{{ $m['name'] }}</td>
                            <td class="px-4 py-3">{{ $m['email'] }}</td>
                            <td class="px-4 py-3">
                                @if ($m['has_set_kpi'])
                                    <span
                                        class="text-xs px-2.5 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Sudah
                                        Set</span>
                                @else
                                    <span
                                        class="text-xs px-2.5 py-0.5 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Belum
                                        Set</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 flex items-center justify-end">
                                <button id="member-{{ $m['id'] }}-button"
                                    data-dropdown-toggle="member-{{ $m['id'] }}-dropdown"
                                    class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                    type="button">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                    </svg>
                                </button>
                                <div id="member-{{ $m['id'] }}-dropdown"
                                    class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                    <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                        aria-labelledby="member-{{ $m['id'] }}-button">
                                        <li>
                                            <a href="{{ route('tl.kpis.items', ['user' => $m['id']]) }}"
                                                class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Kelola
                                                KPI</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('tl.user.analytics', ['user' => $m['id']]) }}"
                                                class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Analitik</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t dark:border-gray-700">
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                @if ($search)
                                    Tidak ada staff yang cocok dengan "{{ $search }}".
                                @else
                                    Tidak ada anggota.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @script
        <script>
            // Reinitialize Flowbite untuk dropdown
            Livewire.hook('morph.updated', () => {
                if (typeof initFlowbite === 'function') {
                    initFlowbite();
                }
            });
        </script>
    @endscript
</section>
