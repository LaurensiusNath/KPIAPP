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
        <h1>Laporan Dashboard Team Leader</h1>
        <p>{{ $division->name }}</p>
    </div>

    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">Periode</span>
            <span class="meta-value">Semester {{ $period->semester }} - {{ $period->year }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Bulan Laporan</span>
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
                <span class="summary-label">Total Staff</span>
                <span class="summary-value">{{ $evaluationStatus['total_staff'] ?? 0 }}</span>
            </td>
            <td>
                <span class="summary-label">Dinilai {{ $monthLabel }}</span>
                <span class="summary-value">{{ $evaluationStatus['evaluated_staff'] ?? 0 }}</span>
            </td>
            <td>
                <span class="summary-label">Rata-rata Divisi</span>
                <span
                    class="summary-value">{{ $divisionStats['division_average'] !== null ? number_format($divisionStats['division_average'], 2) : '—' }}</span>
            </td>
        </tr>
    </table>

    <h2>Status Appraisal</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;" class="text-center">Pending Team Leader</th>
                <th style="width: 25%;" class="text-center">Pending HRD</th>
                <th style="width: 25%;" class="text-center">Finalized</th>
                <th style="width: 25%;" class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ $appraisalStatus['pending_teamleader'] ?? 0 }}</td>
                <td class="text-center">{{ $appraisalStatus['pending_hrd'] ?? 0 }}</td>
                <td class="text-center">{{ $appraisalStatus['finalized'] ?? 0 }}</td>
                <td class="text-center">{{ $appraisalStatus['total'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    @if (!empty($topPerformers))
        <h2>Top Performer {{ $monthLabel }}</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;" class="text-center">Rank</th>
                    <th style="width: 35%;">Nama</th>
                    <th style="width: 40%;">Email</th>
                    <th style="width: 15%;" class="text-center">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topPerformers as $index => $performer)
                    <tr>
                        <td class="text-center">
                            <span style="font-weight: bold; color: #1e40af;">{{ $index + 1 }}</span>
                        </td>
                        <td>{{ $performer['name'] }}</td>
                        <td>{{ $performer['email'] }}</td>
                        <td class="text-center">{{ number_format($performer['score'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Summary Anggota Tim</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Nama</th>
                <th style="width: 40%;">Email</th>
                <th style="width: 20%;" class="text-center">Nilai {{ $monthLabel }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffSummary as $index => $staff)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $staff['name'] }}</td>
                    <td>{{ $staff['email'] }}</td>
                    <td class="text-center">
                        {{ $staff['monthly_average'] !== null ? number_format($staff['monthly_average'], 2) : 'Belum dinilai' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Belum ada data anggota</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem KPI App.</p>
    </div>
</body>

</html>
