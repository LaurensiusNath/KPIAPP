<section class="bg-gray-50 dark:bg-gray-900 p-6 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        <header class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->mode === 'monthly' ? 'Penilaian KPI Bulanan' : 'Anggota Divisi' }}
                    </h1>
                    @if ($this->mode === 'monthly')
                        <p class="text-gray-500 dark:text-gray-400">Status penilaian bulan {{ now()->format('F') }}</p>
                    @endif
                </div>
            </div>
        </header>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Status Bulan Ini</th>
                        <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $m)
                        <tr class="border-t dark:border-gray-700">
                            <td class="px-4 py-3">{{ $m['name'] }}</td>
                            <td class="px-4 py-3">{{ $m['email'] }}</td>
                            <td class="px-4 py-3">
                                @if ($m['status'] === 'Submitted')
                                    <span
                                        class="text-xs px-2.5 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Submitted</span>
                                @else
                                    <span
                                        class="text-xs px-2.5 py-0.5 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if ($this->mode === 'monthly')
                                        <a href="{{ route('tl.kpi.monthly', ['user' => $m['id']]) }}"
                                            class="px-3 py-1 text-xs rounded bg-primary-700 hover:bg-primary-800 text-white">Nilai
                                            Bulanan</a>
                                    @else
                                        <a href="{{ route('tl.kpi.items', ['user' => $m['id']]) }}"
                                            class="px-3 py-1 text-xs rounded bg-primary-700 hover:bg-primary-800 text-white">Kelola
                                            KPI</a>
                                    @endif
                                    <a href="{{ route('tl.user.analytics', ['user' => $m['id']]) }}"
                                        class="px-3 py-1 text-xs rounded border border-primary-600 text-primary-700 hover:bg-primary-50 dark:text-primary-300 dark:border-primary-400 dark:hover:bg-gray-700">Analitik</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t dark:border-gray-700">
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                anggota.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
