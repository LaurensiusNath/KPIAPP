<div class="p-6 space-y-6">
    @if (session('success'))
        <div class="p-3 text-sm rounded-lg bg-green-50 text-green-700 dark:bg-green-900 dark:text-green-200">
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 text-sm rounded-lg bg-red-50 text-red-700 dark:bg-red-900 dark:text-red-200">
            {{ session('error') }}</div>
    @endif
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Daftar Appraisal Semester</h1>
        <div class="flex items-center gap-3">
            <label class="text-sm text-gray-600 dark:text-gray-300">Periode</label>
            <select wire:model.live="periodId" wire:change="changePeriod($event.target.value)" wire:loading.attr="disabled"
                class="text-sm rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                @foreach ($this->periods as $p)
                    <option value="{{ $p->id }}">{{ $p->year }} - Semester {{ $p->semester }} @if ($p->is_active)
                            (Aktif)
                        @endif
                    </option>
                @endforeach
            </select>
            <button type="button" wire:click="refresh"
                class="px-3 py-1.5 text-xs rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">Refresh</button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2 text-left">Nama</th>
                    <th class="px-4 py-2 text-left">Divisi</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse($users as $u)
                    @php($app = $appraisals[$u->id] ?? null)
                    @php($status = 'Belum dinilai')
                    @if ($app)
                        @if (!$app->teamleader_submitted_at)
                            @php($status = 'Menunggu TL')
                        @elseif(!$app->hrd_submitted_at)
                            @php($status = 'Menunggu HRD')
                        @elseif($app->teamleader_submitted_at && $app->hrd_submitted_at && !$app->is_finalized)
                            @php($status = 'HRD Submitted')
                        @elseif($app->is_finalized)
                            @php($status = 'Finalized')
                        @endif
                    @endif
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $u->name }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $u->division?->name ?? '-' }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                @class([
                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' =>
                                        $status === 'Belum dinilai',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200' =>
                                        $status === 'Menunggu TL',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200' =>
                                        $status === 'Menunggu HRD' || $status === 'HRD Submitted',
                                    'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200' =>
                                        $status === 'Finalized',
                                ])">{{ $status }}</span>
                        </td>
                        <td class="px-4 py-2">
                            @if ($periodId)
                                <button type="button" wire:click="goToAppraisal({{ $u->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white">
                                    @if ($app)
                                        Isi Appraisal
                                    @else
                                        Lihat
                                    @endif
                                </button>
                            @else
                                <span class="text-xs text-gray-400">Pilih periode</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Tidak ada
                            user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            {{ $users->links() }}
        </div>
    </div>
</div>
