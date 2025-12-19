<tr class="border-b dark:border-gray-700" wire:click.away="closeMenu">
    <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
        {{ $division->name }}</th>
    <td class="px-4 py-3">{{ $leaderName ?? '-' }}</td>
    <td class="px-4 py-3">{{ $memberCount }}</td>
    <td class="px-4 py-3 flex items-center justify-end relative">
        <button type="button" wire:click="toggleMenu"
            class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100">
            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
            </svg>
        </button>
        @if ($menuOpen)
            <div
                class="absolute right-0 z-20 mt-2 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                    <li>
                        <a href="{{ route('admin.division.analytics', $division) }}"
                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Show</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.division.detail', $division) }}"
                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Edit</a>
                    </li>
                    <li>
                        <button type="button" wire:click="confirmDelete"
                            class="w-full text-left block py-2 px-4 text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-600">Delete</button>
                    </li>
                </ul>
                <div class="py-1">
                    @if ($confirmingDelete)
                        <div class="px-4 py-2 space-y-2">
                            <p class="text-xs text-gray-600 dark:text-gray-300">Hapus divisi ini? Semua anggota akan
                                dilepas.</p>
                            <div class="flex gap-2 text-xs">
                                <button type="button" wire:click="deleteDivision"
                                    class="flex-1 px-2 py-1 rounded bg-red-600 text-white">Ya</button>
                                <button type="button" wire:click="cancelDelete"
                                    class="flex-1 px-2 py-1 rounded border border-gray-300 dark:border-gray-500">Tidak</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </td>
</tr>
