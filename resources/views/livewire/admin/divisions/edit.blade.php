<section class=" dark:bg-gray-900  min-h-screen">
    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Division Header -->
        <header class="space-y-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $divisionData->name }}</h1>
            <p class="text-gray-500 dark:text-gray-400">Division Management</p>
        </header>

        <!-- Leader Card -->
        <x-admin.division-leader-card :leader="$this->leader" />

        <!-- Staff Header + Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Staff Members</h2>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="openAddUserModal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-primary-700 hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Add Staff
                </button>
                <button type="button" wire:click="openChangeLeaderModal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-800">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Change Leader
                </button>
            </div>
        </div>

        <!-- Staff Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">Name</th>
                        <th scope="col" class="px-4 py-3">Email</th>
                        <th scope="col" class="px-4 py-3">Role</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->staff as $user)
                        <x-admin.division-users-row :user="$user" />
                    @empty
                        <tr class="border-t dark:border-gray-700">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No staff
                                members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($this->staff->hasPages())
                <div class="p-4 border-t dark:border-gray-700">
                    {{ $this->staff->links() }}
                </div>
            @endif
        </div>

        <!-- Change Leader Modal (Livewire-controlled, no Flowbite JS) -->
        @if ($showChangeLeaderModal)
            <div
                class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50">
                <div class="relative p-4 w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Change Team Leader</h3>
                            <button type="button" wire:click="$set('showChangeLeaderModal', false)"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="changeLeader" class="p-4 md:p-5">
                            <div class="mb-4">
                                <label for="newLeader"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select New
                                    Leader</label>
                                <select wire:model="selectedNewLeaderId" id="newLeader"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option value="">Choose a user</option>
                                    @foreach ($this->availableLeaders as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedNewLeaderId')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit"
                                class="text-white inline-flex items-center bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800">
                                Change Leader
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Add User Modal (Livewire-controlled, no Flowbite JS) -->
        @if ($showAddUserModal)
            <div
                class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50">
                <div class="relative p-4 w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Staff Member</h3>
                            <button type="button" wire:click="$set('showAddUserModal', false)"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <form wire:submit.prevent="addUserToDivision" class="p-4 md:p-5" x-data="{ search: '' }">
                            <div class="mb-4">
                                <label class="block mb-3 text-sm font-medium text-gray-900 dark:text-white">
                                    Select Users to Add
                                </label>

                                <!-- Error Message (show at top if validation fails) -->
                                @error('selectedUsers')
                                    <div
                                        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">{{ $message }}</span>
                                        </div>
                                    </div>
                                @enderror

                                @if ($this->availableUsers->count() > 0)
                                    <!-- Search Box -->
                                    <div class="mb-3">
                                        <input type="text" x-model="search" placeholder="Search users..."
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                                    </div>

                                    <!-- User List with Checkboxes -->
                                    <div
                                        class="max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg mb-3">
                                        @foreach ($this->availableUsers as $user)
                                            <label
                                                x-show="search === '' || '{{ strtolower($user->name . ' ' . $user->email) }}'.includes(search.toLowerCase())"
                                                class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                                wire:key="available-user-{{ $user->id }}">
                                                <input type="checkbox" wire:model="selectedUsers"
                                                    value="{{ $user->id }}"
                                                    class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                                <div class="ml-3 flex-1">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $user->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $user->email }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">No available users to
                                        add
                                    </p>
                                @endif
                            </div>

                            @if ($this->availableUsers->count() > 0)
                                <button type="submit" wire:target="addUserToDivision" wire:loading.attr="disabled"
                                    class="w-full text-white inline-flex justify-center items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span wire:loading.remove wire:target="addUserToDivision">
                                        Add Users
                                    </span>
                                    <span wire:loading wire:target="addUserToDivision">Adding...</span>
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

</section>
