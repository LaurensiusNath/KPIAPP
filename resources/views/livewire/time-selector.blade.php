<div class="flex items-center gap-2">
    <!-- Time Selector Trigger -->
    <div class="relative">
        <button id="timeDropdownButton" data-dropdown-toggle="timeDropdown"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors"
            type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="hidden sm:inline">
                @if ($currentTestTime)
                    <span class="text-orange-600 dark:text-orange-400 font-semibold">TEST MODE</span>
                @else
                    Time
                @endif
            </span>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown menu -->
        <div id="timeDropdown"
            class="hidden z-50 w-80 bg-white rounded-lg shadow-lg dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    🧪 Testing Time Control
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pilih tanggal & jam bebas, atau gunakan preset.
                </p>
            </div>

            <!-- Custom date/time picker -->
            <div class="p-4 space-y-3">
                <label class="block">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Tanggal & Jam</span>
                    <input type="datetime-local" wire:model="customDateTime"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-500 dark:bg-gray-600 dark:text-white focus:border-blue-500 focus:ring-blue-500" />
                    @error('customDateTime')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>

                <div class="flex gap-2">
                    <button type="button" wire:click="apply"
                        class="flex-1 px-3 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        ✅ Terapkan
                    </button>
                    <button type="button" wire:click="resetToRealTime"
                        class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-md transition-colors">
                        🔄 Real Time
                    </button>
                </div>
            </div>

            <!-- Quick presets -->
            <div class="px-4 pb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    Preset cepat
                </p>
                <div class="space-y-1">
                    @foreach ($quickPresets as $value => $label)
                        <button type="button" wire:click="applyPreset('{{ $value }}')"
                            class="w-full text-left px-2 py-1.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded transition-colors">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($currentTestTime)
                <div
                    class="px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border-t border-orange-200 dark:border-orange-800">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-orange-800 dark:text-orange-200 font-medium">
                            Test: {{ $currentTestTime }}
                        </span>
                    </div>
                    <button onclick="window.location.reload()"
                        class="mt-2 w-full px-3 py-1.5 text-xs font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md transition-colors">
                        🔄 Refresh Halaman
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Visual Badge (Always Visible) -->
    @if ($currentTestTime)
        <div
            class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 dark:bg-orange-900/30 border border-orange-300 dark:border-orange-700 rounded-full">
            <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
            <span class="text-xs font-medium text-orange-800 dark:text-orange-200">
                {{ $currentTestTime }}
            </span>
        </div>
    @endif
</div>
