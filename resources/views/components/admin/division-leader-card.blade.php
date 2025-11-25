@props(['leader'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Team Leader</h3>
    </div>

    @if ($leader)
        <div class="flex items-center space-x-4">
            <div
                class="flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-bold text-lg">
                {{ strtoupper(substr($leader->name, 0, 1)) }}
            </div>
            <div>
                <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ $leader->name }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $leader->email }}</p>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 mt-1">
                    Team Leader
                </span>
            </div>
        </div>
    @else
        <div class="text-center py-4">
            <p class="text-gray-500 dark:text-gray-400">No leader assigned</p>
        </div>
    @endif
</div>
