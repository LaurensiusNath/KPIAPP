<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Appraisal Divisi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1f2937;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #2563eb;
            border-radius: 6px;
        }

        .header h1 {
            font-size: 20pt;
            margin-bottom: 6px;
            color: #ffffff;
            font-weight: bold;
        }

        .header p {
            font-size: 12pt;
            color: #dbeafe;
            font-weight: 600;
        }

        .meta {
            margin-bottom: 16px;
            padding: 0;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
        }

        .meta-row {
            display: flex;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .meta-row:last-child {
            border-bottom: none;
        }

        .meta-row:nth-child(even) {
            background: #f9fafb;
        }

        .meta-label {
            width: 140px;
            font-weight: bold;
            color: #374151;
            font-size: 9pt;
        }

        .meta-value {
            color: #1f2937;
            font-size: 9pt;
            flex: 1;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            gap: 12px;
        }

        .summary-card {
            flex: 1;
            padding: 14px;
            background: #dbeafe;
            border: 2px solid #93c5fd;
            border-radius: 6px;
            text-align: center;
        }

        .summary-label {
            font-size: 8pt;
            color: #1e40af;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 22pt;
            font-weight: bold;
            color: #1e40af;
        }

        h2 {
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #1e40af;
            border-left: 4px solid #2563eb;
            padding-left: 10px;
            font-weight: bold;
            background: #eff6ff;
            padding: 8px 10px;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #d1d5db;
        }

        table thead {
            background: #2563eb;
        }

        table th {
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            color: #ffffff;
            font-size: 8pt;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table th:last-child {
            border-right: none;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9pt;
            border-right: 1px solid #f3f4f6;
        }

        table td:last-child {
            border-right: none;
        }

        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table tbody tr:hover {
            background: #eff6ff;
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
            margin-top: 24px;
            padding-top: 10px;
            border-top: 2px solid #d1d5db;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
        }

        .progress-bar {
            background: #eff6ff;
            height: 22px;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #bfdbfe;
            position: relative;
        }

        .progress-fill {
            background: #2563eb;
            height: 100%;
            float: left;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #86efac;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Appraisal Divisi</h1>
        <p>{{ $division->name }}</p>
    </div>

    <div class="meta">
        <div class="meta-row">
            <span class="meta-label">Team Leader</span>
            <span class="meta-value">{{ $division->leader?->name ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Periode</span>
            <span class="meta-value">Semester {{ $period->semester }} - {{ $period->year }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Bulan</span>
            <span class="meta-value">
                @php
                    $months = $period->semester === 1 ? range(1, 6) : range(7, 12);
                    $monthNames = array_map(
                        fn($m) => \Carbon\Carbon::create($period->year, $m, 1)->translatedFormat('F'),
                        $months,
                    );
                @endphp
                {{ implode(', ', $monthNames) }}
            </span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Tanggal Cetak</span>
            <span class="meta-value">{{ $generatedAt }}</span>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="summary-label">Rata-rata KPI (6 Bulan)</div>
            <div class="summary-value">
                {{ $summary['overall_average'] !== null ? number_format($summary['overall_average'], 2) : '—' }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Jumlah Staff</div>
            <div class="summary-value">{{ $summary['staff_count'] }}</div>
        </div>
    </div>

    <h2>Tren KPI (Semester {{ $period->semester }})</h2>
    @php
        $chartMonths = $period->semester === 1 ? range(1, 6) : range(7, 12);
        $monthLookup = collect($trendSeries)->keyBy('month');
    @endphp
    <table>
        <thead>
            <tr>
                <th style="width: 100px; text-align: left;">Bulan</th>
                <th style="width: 60px;">Nilai</th>
                <th style="text-align: left;">Visualisasi</th>
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
                    <td style="vertical-align: middle; font-weight: 600; padding: 10px 8px;">{{ $label }}</td>
                    <td class="text-center" style="vertical-align: middle; padding: 10px 8px;">
                        @if ($value !== null)
                            <strong style="font-size: 11pt; color: #1e40af;">{{ number_format($value, 2) }}</strong>
                        @else
                            <span style="color: #9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="vertical-align: middle; padding: 10px 8px;">
                        @if ($value !== null)
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $percentage }}%;"></div>
                            </div>
                        @else
                            <span style="color: #9ca3af; font-size: 8pt; font-style: italic;">Belum ada data</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Daftar Staff & Rata-rata KPI</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 160px; text-align: left;">Nama Staff</th>
                <th style="width: 160px; text-align: left;">Email</th>
                <th style="width: 70px;">Rata-rata</th>
                <th style="width: 90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffList as $index => $staff)
                <tr>
                    <td class="text-center" style="vertical-align: middle; padding: 10px 8px;">{{ $index + 1 }}</td>
                    <td style="vertical-align: middle; font-weight: 600; padding: 10px 8px;">{{ $staff['name'] }}</td>
                    <td style="vertical-align: middle; font-size: 8pt; color: #6b7280; padding: 10px 8px;">
                        {{ $staff['email'] }}</td>
                    <td class="text-center" style="vertical-align: middle; padding: 10px 8px;">
                        @if ($staff['average_score'] !== null)
                            <strong
                                style="font-size: 12pt; color: #1e40af;">{{ number_format($staff['average_score'], 2) }}</strong>
                        @else
                            <span style="color: #9ca3af; font-size: 8pt; font-style: italic;">Belum dinilai</span>
                        @endif
                    </td>
                    <td class="text-center" style="vertical-align: middle; padding: 10px 8px;">
                        <span
                            class="badge {{ $staff['appraisal_status'] === 'Finalized' ? 'badge-success' : 'badge-warning' }}">
                            {{ $staff['appraisal_status'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 20px;">Belum ada data staff</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem KPI App.</p>
    </div>
</body>

</html>
