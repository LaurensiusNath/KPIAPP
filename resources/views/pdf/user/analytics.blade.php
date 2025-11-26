<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analitik KPI Saya</title>
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
            border-bottom: 2px solid #16a34a;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 4px;
            color: #15803d;
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
            width: 50%;
            padding: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
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
            color: #15803d;
            display: block;
        }

        h2 {
            font-size: 13pt;
            margin-top: 24px;
            margin-bottom: 12px;
            color: #15803d;
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
        <h1>Laporan Analitik KPI Saya</h1>
        <p>{{ $user->name }}</p>
    </div>

    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">Email</span>
            <span class="meta-value">{{ $user->email }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Divisi</span>
            <span class="meta-value">{{ $user->division?->name ?? '—' }}</span>
        </div>
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

    <table class="summary">
        <tr>
            <td>
                <span class="summary-label">Rata-rata KPI Bulan Ini</span>
                <span
                    class="summary-value">{{ $monthlyAverage !== null ? number_format($monthlyAverage, 2) : '—' }}</span>
            </td>
            <td>
                <span class="summary-label">Jumlah KPI</span>
                <span class="summary-value">{{ count($kpiRows) }}</span>
            </td>
        </tr>
    </table>

    <h2>Detail KPI</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 30%;">Nama KPI</th>
                <th style="width: 10%;" class="text-center">Bobot</th>
                <th style="width: 15%;" class="text-center">Skor</th>
                <th style="width: 40%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kpiRows as $index => $kpi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $kpi['title'] }}</td>
                    <td class="text-center">{{ $kpi['weight'] }}%</td>
                    <td class="text-center">
                        @if ($kpi['score'] !== null)
                            {{ number_format($kpi['score'], 2) }}
                            @if ($kpi['criteria_label'])
                                <br><span style="font-size: 9pt; color: #6b7280;">({{ $kpi['criteria_label'] }})</span>
                            @endif
                        @else
                            Belum dinilai
                        @endif
                    </td>
                    <td style="font-size: 9pt;">{{ $kpi['note'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Belum ada data KPI</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem KPI App.</p>
    </div>
</body>

</html>
