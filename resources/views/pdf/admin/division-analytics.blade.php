<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analitik Divisi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1f2937;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #2563eb;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 4px;
            color: #1e40af;
        }

        .header p {
            font-size: 10pt;
            color: #6b7280;
        }

        .meta {
            margin-bottom: 20px;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 4px;
        }

        .meta-row {
            margin-bottom: 6px;
            overflow: hidden;
        }

        .meta-label {
            float: left;
            width: 140px;
            font-weight: bold;
            color: #374151;
        }

        .meta-value {
            color: #1f2937;
            margin-left: 140px;
        }

        .summary {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .summary td {
            width: 33.33%;
            padding: 12px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            text-align: center;
            vertical-align: middle;
        }

        .summary-label {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 4px;
            display: block;
        }

        .summary-value {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
            display: block;
        }

        h2 {
            font-size: 13pt;
            margin-top: 24px;
            margin-bottom: 12px;
            color: #1e40af;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table thead {
            background: #f9fafb;
        }

        table.data-table th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 10pt;
            vertical-align: middle;
        }

        table.data-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 10pt;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #9ca3af;
        }

        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Analitik Divisi</h1>
        <p>{{ $division->name }}</p>
    </div>

    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">Periode</span>
            <span class="meta-value">Semester {{ $period->semester }} - {{ $period->year }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Bulan</span>
            <span class="meta-value">{{ $monthLabel }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Tanggal Cetak</span>
            <span class="meta-value">{{ now()->translatedFormat('d F Y H:i') }}</span>
        </div>
    </div>

    <div class="summary">

        <div class="summary-card">
            <div class="summary-label">Anggota Dinilai</div>
            <table class="summary">
                <tr>
                    <td>
                        <span class="summary-label">Rata-rata KPI Bulan Ini</span>
                        <span
                            class="summary-value">{{ $divisionAverage !== null ? number_format($divisionAverage, 2) : '—' }}</span>
                    </td>
                    <td>
                        <span class="summary-label">Anggota Dinilai</span>
                        <span
                            class="summary-value">{{ collect($userScores)->filter(fn($user) => $user['avg_score'] !== null)->count() }}</span>
                    </td>
                    <td>
                        <span class="summary-label">Total Anggota</span>
                        <span class="summary-value">{{ count($userScores) }}</span>
                    </td>
                </tr>
            </table>

            <h2>Daftar Nilai KPI per Anggota</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 35%;">Nama</th>
                        <th style="width: 40%;">Email</th>
                        <th style="width: 20%;" class="text-center">Rata-rata KPI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($userScores as $index => $user)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $user['name'] }}</td>
                            <td>{{ $user['email'] }}</td>
                            <td class="text-center">
                                {{ $user['avg_score'] !== null ? number_format($user['avg_score'], 2) : 'Belum dinilai' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data penilaian</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if (!empty($trendSeries))
                <h2>Tren KPI (Semester {{ $period->semester }})</h2>
                @php
                    $chartMonths = $period->semester === 1 ? range(1, 6) : range(7, 12);
                    $monthLookup = collect($trendSeries)->keyBy('month');
                    $maxValue = collect($trendSeries)->max('average') ?? 5;
                @endphp
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Bulan</th>
                            <th style="width: 15%;" class="text-center">Nilai</th>
                            <th style="width: 65%;">Visualisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chartMonths as $month)
                            @php
                                $data = $monthLookup->get($month);
                                $value = $data['average'] ?? null;
                                $label = \Carbon\Carbon::create($period->year, $month, 1)->translatedFormat('F');
                                $percentage = $value !== null ? ($value / 5) * 100 : 0;
                            @endphp
                            <tr>
                                <td>{{ $label }}</td>
                                <td style="text-align: center;">{{ $value !== null ? number_format($value, 2) : '—' }}
                                </td>
                                <td>
                                    @if ($value !== null)
                                        <div
                                            style="background: #eff6ff; height: 20px; border-radius: 3px; overflow: hidden;">
                                            <div
                                                style="background: #2563eb; height: 100%; width: {{ $percentage }}%; float: left;">
                                            </div>
                                        </div>
                                    @else
                                        <span style="color: #9ca3af; font-size: 9pt;">Belum ada data</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="footer">
                <p>Laporan ini dibuat secara otomatis oleh sistem KPI App.</p>
            </div>
</body>

</html>
