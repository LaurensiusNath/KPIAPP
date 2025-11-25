<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center">
                        <div>
                            <a href="{{ route('admin.appraisal.divisions.show', ['division' => $user->division_id, 'period' => $period->id]) }}"
                                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 mb-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Kembali ke Appraisal Divisi
                            </a>
                            <h2 class="text-2xl font-semibold text-gray-800">Appraisal: {{ $user->name }}</h2>
                            <p class="text-sm text-gray-600 mt-1">Semester {{ $period->semester }} - {{ $period->year }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('admin.appraisal.form', ['user' => $user->id, 'period' => $period->id]) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Isi Appraisal
                            </a>
                            <button wire:click="downloadReport"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Download Laporan PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Staff</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Email</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Divisi</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->division?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Role</p>
                            <p class="text-sm text-gray-900 mt-1">{{ ucfirst($user->role) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail KPI per Bulan</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        KPI</th>
                                    @foreach ($detail['months'] as $month)
                                        <th scope="col"
                                            class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ \Carbon\Carbon::create($period->year, $month, 1)->translatedFormat('M') }}
                                        </th>
                                    @endforeach
                                    <th scope="col"
                                        class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Avg</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($detail['kpis'] as $kpi)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $kpi['title'] }}</div>
                                            <div class="text-xs text-gray-500">Bobot: {{ $kpi['weight'] }}%</div>
                                        </td>
                                        @foreach ($detail['months'] as $month)
                                            <td class="px-3 py-4 text-center">
                                                @php
                                                    $score = $kpi['monthly_scores'][$month]['score'] ?? null;
                                                    $note = $kpi['monthly_scores'][$month]['note'] ?? null;
                                                @endphp
                                                @if ($score !== null)
                                                    <span
                                                        class="text-sm font-semibold text-gray-900">{{ $score }}</span>
                                                    @if ($note)
                                                        <div class="text-xs text-gray-500 mt-1"
                                                            title="{{ $note }}">{{ Str::limit($note, 20) }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-gray-400">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-4 text-center">
                                            @if ($kpi['average'] !== null)
                                                <span
                                                    class="text-sm font-bold text-blue-600">{{ number_format($kpi['average'], 2) }}</span>
                                            @else
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($detail['months']) + 2 }}"
                                            class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data KPI</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Appraisal Status -->
            @if ($detail['appraisal'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Appraisal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status Team Leader</p>
                                <p class="text-sm text-gray-900 mt-1">
                                    @if ($detail['appraisal']->teamleader_submitted_at)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Sudah Submit
                                            ({{ \Carbon\Carbon::parse($detail['appraisal']->teamleader_submitted_at)->translatedFormat('d M Y') }})
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Belum Submit
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status HRD</p>
                                <p class="text-sm text-gray-900 mt-1">
                                    @if ($detail['appraisal']->hrd_submitted_at)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Sudah Submit
                                            ({{ \Carbon\Carbon::parse($detail['appraisal']->hrd_submitted_at)->translatedFormat('d M Y') }})
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Belum Submit
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status Finalisasi</p>
                                <p class="text-sm text-gray-900 mt-1">
                                    @if ($detail['appraisal']->is_finalized)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Finalized
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            In Progress
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($detail['appraisal']->comment_teamleader || $detail['appraisal']->comment_hrd)
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Komentar</h4>
                                @if ($detail['appraisal']->comment_teamleader)
                                    <div class="mb-3">
                                        <p class="text-xs font-medium text-gray-500">Team Leader:</p>
                                        <p class="text-sm text-gray-700 mt-1 bg-gray-50 p-3 rounded">
                                            {{ $detail['appraisal']->comment_teamleader }}</p>
                                    </div>
                                @endif
                                @if ($detail['appraisal']->comment_hrd)
                                    <div>
                                        <p class="text-xs font-medium text-gray-500">HRD:</p>
                                        <p class="text-sm text-gray-700 mt-1 bg-gray-50 p-3 rounded">
                                            {{ $detail['appraisal']->comment_hrd }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
