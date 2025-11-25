<div class="antialiased bg-gray-50 dark:bg-gray-900">
    <nav
        class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50">
        <div class="flex flex-wrap justify-between items-center">
            <div class="flex items-center">
                <button data-drawer-target="drawer-navigation" data-drawer-toggle="drawer-navigation"
                    aria-controls="drawer-navigation"
                    class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                    <svg aria-hidden="true" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Toggle sidebar</span>
                </button>
                <a href="{{ route('user.analytics') }}" class="flex items-center gap-2 mr-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m6-6H6m9 8H9a4 4 0 01-4-4V8a4 4 0 014-4h6a4 4 0 014 4v8a4 4 0 01-4 4z" />
                    </svg>
                    <span class="text-xl font-semibold text-gray-900 dark:text-white">Portal Karyawan</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden md:flex flex-col text-right">
                    <span class="text-sm font-semibold text-gray-700 dark:text-white">{{ auth()->user()?->name }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">{{ auth()->user()?->email }}</span>
                </div>
                <a href="{{ route('profile.edit') }}"
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-100 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">Profil</a>
                <button type="button" wire:click="logout"
                    class="px-3 py-1.5 text-sm font-medium text-white bg-primary-700 rounded-lg hover:bg-primary-800">Logout</button>
            </div>
        </div>
    </nav>

    <aside
        class="fixed top-0 left-0 z-40 w-64 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
        aria-label="Sidenav" id="drawer-navigation">
        <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
            @php
                $linkClass = function (bool $active) {
                    return $active
                        ? 'flex items-center gap-3 p-2 rounded-lg text-sm font-medium bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-white'
                        : 'flex items-center gap-3 p-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700';
                };
            @endphp
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('user.analytics') }}"
                        class="{{ $linkClass(request()->routeIs('user.analytics')) }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8a2 2 0 002-2v-5h3l-3-3" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.analytics') }}"
                        class="{{ $linkClass(request()->routeIs('user.analytics')) }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 6h9m0 0v9m0-9l-9 9-4-4-6 6" />
                        </svg>
                        <span>Analitik KPI</span>
                    </a>
                </li>
            </ul>
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" wire:click="logout"
                    class="flex items-center gap-3 w-full p-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                    </svg>
                    Keluar
                </button>
            </div>
        </div>
    </aside>
</div>
