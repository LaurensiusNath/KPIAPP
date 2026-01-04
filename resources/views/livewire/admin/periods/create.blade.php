<form wire:submit.prevent="create" class="p-4 space-y-4">
    @error('create')
        <div class="p-3 bg-red-100 text-red-700 rounded dark:bg-red-900 dark:text-red-200">{{ $message }}</div>
    @enderror
    <div>
        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Year</label>
        <input type="number" wire:model.defer="year"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 p-2.5">
        @error('year')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>
    <div>
        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Semester</label>
        <select wire:model.defer="semester"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 p-2.5">
            <option value="">Choose</option>
            <option value="1">1 (Jan–Jun)</option>
            <option value="2">2 (Jul–Dec)</option>
        </select>
        @error('semester')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>
    <div class="flex justify-end gap-2">
        <button type="button" wire:click="cancel"
            class="px-4 py-2 rounded border dark:border-gray-700 dark:text-gray-100">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-primary-700 text-white hover:bg-primary-800">Create</button>
    </div>
</form>
