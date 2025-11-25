<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Appraisal Staff</title>
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
            display: flex;
            margin-bottom: 6px;
        }

        .meta-label {
            width: 140px;
            font-weight: bold;
            color: #374151;
        }

        .meta-value {
            color: #1f2937;
        }

        h2 {
            font-size: 13pt;
            margin-top: 24px;
            margin-bottom: 12px;
            color: #15803d;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }

        table thead {
            background: #f9fafb;
        }

        table th {
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #d1d5db;
        }

        table td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tbody tr:last-child td {
            border-bottom: none;
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
        <h1>Laporan Appraisal Staff</h1>
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
            <span class="meta-label">Tanggal Cetak</span>
            <span class="meta-value">{{ $generatedAt }}</span>
        </div>
    </div>

    <h2>Detail KPI per Bulan</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 120px;">KPI</th>
                @foreach ($detail['months'] as $month)
                    <th class="text-center" style="width: 60px;">
                        {{ \Carbon\Carbon::create($period->year, $month, 1)->translatedFormat('M') }}
                    </th>
                @endforeach
                <th class="text-center" style="width: 60px;">Avg</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detail['kpis'] as $kpi)
                <tr>
                    <td>
                        {{ $kpi['title'] }}<br>
                        <span style="font-size: 8pt; color: #6b7280;">({{ $kpi['weight'] }}%)</span>
                    </td>
                    @foreach ($detail['months'] as $month)
                        <td class="text-center">
                            {{ $kpi['monthly_scores'][$month]['score'] ?? '—' }}
                        </td>
                    @endforeach
                    <td class="text-center" style="font-weight: bold;">
                        {{ $kpi['average'] !== null ? number_format($kpi['average'], 2) : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($detail['months']) + 2 }}" class="text-center text-muted">Belum ada data KPI
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($detail['appraisal'])
        <h2>Status Appraisal</h2>
        <div class="meta">
            <div class="meta-row">
                <span class="meta-label">Team Leader</span>
                <span
                    class="meta-value">{{ $detail['appraisal']->teamleader_submitted_at ? 'Sudah Submit' : 'Belum Submit' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">HRD</span>
                <span
                    class="meta-value">{{ $detail['appraisal']->hrd_submitted_at ? 'Sudah Submit' : 'Belum Submit' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Status</span>
                <span class="meta-value">{{ $detail['appraisal']->is_finalized ? 'Finalized' : 'In Progress' }}</span>
            </div>
            @if ($detail['appraisal']->comment_teamleader)
                <div class="meta-row">
                    <span class="meta-label">Komentar TL</span>
                    <span class="meta-value">{{ $detail['appraisal']->comment_teamleader }}</span>
                </div>
            @endif
            @if ($detail['appraisal']->comment_hrd)
                <div class="meta-row">
                    <span class="meta-label">Komentar HRD</span>
                    <span class="meta-value">{{ $detail['appraisal']->comment_hrd }}</span>
                </div>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem KPI App.</p>
    </div>
</body>

</html>
