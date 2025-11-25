@props(['user'])

<tr class="border-b dark:border-gray-700">
    <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
        {{ $user->name }}
    </th>
    <td class="px-4 py-3">{{ $user->email }}</td>
    <td class="px-4 py-3">
        <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
            User
        </span>
    </td>
    <td class="px-4 py-3 flex items-center justify-end">
        <button
            onclick="if(confirm('Are you sure you want to remove {{ addslashes($user->name) }} from this division?')) { @this.call('removeUserFromDivision', {{ $user->id }}) }"
            class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
            Remove
        </button>
    </td>
</tr>
