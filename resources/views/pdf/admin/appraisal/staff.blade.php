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
            font-size: 10pt;
            line-height: 1.4;
            color: #1f2937;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #16a34a;
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
            color: #e0f2e9;
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

        .summary-box {
            margin-bottom: 16px;
            padding: 16px;
            background: #dcfce7;
            border: 2px solid #86efac;
            border-radius: 6px;
            text-align: center;
        }

        .summary-label {
            font-size: 9pt;
            color: #166534;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 24pt;
            font-weight: bold;
            color: #15803d;
        }

        h2 {
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #15803d;
            border-left: 4px solid #16a34a;
            padding-left: 10px;
            font-weight: bold;
            background: #f0fdf4;
            padding: 8px 10px;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 8pt;
            border: 1px solid #d1d5db;
        }

        table thead {
            background: #16a34a;
        }

        table th {
            padding: 10px 6px;
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
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #f3f4f6;
        }

        table td:last-child {
            border-right: none;
        }

        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table tbody tr:hover {
            background: #f0fdf4;
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

        .kpi-title {
            font-weight: bold;
            color: #1f2937;
            font-size: 8pt;
            line-height: 1.3;
        }

        .kpi-weight {
            font-size: 7pt;
            color: #16a34a;
            font-weight: 600;
            margin-top: 2px;
        }

        .score-value {
            font-weight: bold;
            font-size: 11pt;
            color: #15803d;
        }

        .criteria-label {
            font-size: 6pt;
            color: #6b7280;
            margin-top: 2px;
            font-style: italic;
        }

        .avg-column {
            background: #dcfce7 !important;
            font-weight: bold;
        }

        .comment-section {
            margin-bottom: 16px;
            padding: 14px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
            border-left: 4px solid #16a34a;
        }

        .comment-title {
            font-size: 10pt;
            font-weight: bold;
            color: #15803d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .comment-content {
            font-size: 9pt;
            color: #374151;
            line-height: 1.6;
            padding: 10px;
            background: #ffffff;
            border-radius: 3px;
            border: 1px solid #d1fae5;
        }

        .comment-empty {
            font-size: 9pt;
            color: #9ca3af;
            font-style: italic;
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
            <span class="meta-label">Role</span>
            <span class="meta-value">{{ ucfirst($user->role) }}</span>
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

    @if (isset($detail['overall_weighted_average']) && $detail['overall_weighted_average'] !== null)
        <div class="summary-box">
            <div class="summary-label">Rata-rata KPI Tertimbang Keseluruhan</div>
            <div class="summary-value">{{ number_format($detail['overall_weighted_average'], 2) }}</div>
        </div>
    @endif

    <h2>Detail KPI per Bulan</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 140px; text-align: left;">KPI</th>
                @foreach ($detail['months'] as $month)
                    <th style="width: 50px;">
                        {{ \Carbon\Carbon::create($period->year, $month, 1)->translatedFormat('M') }}
                    </th>
                @endforeach
                <th style="width: 50px;">AVG</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detail['kpis'] as $kpi)
                <tr>
                    <td style="vertical-align: top; padding: 10px 8px;">
                        <div class="kpi-title">{{ $kpi['title'] }}</div>
                        <div class="kpi-weight">Bobot: {{ $kpi['weight'] }}%</div>
                    </td>
                    @foreach ($detail['months'] as $month)
                        @php
                            $score = $kpi['monthly_scores'][$month]['score'] ?? null;
                            $criteriaScale = $kpi['criteria_scale'] ?? [];
                            $criteria = null;
                            if ($score !== null && is_array($criteriaScale)) {
                                $scoreInt = (int) $score;
                                $criteria = $criteriaScale[$scoreInt] ?? null;
                            }
                        @endphp
                        <td class="text-center" style="vertical-align: middle; padding: 10px 4px;">
                            @if ($score !== null)
                                <div class="score-value">{{ $score }}</div>
                                @if ($criteria)
                                    <div class="criteria-label">{{ $criteria }}</div>
                                @endif
                            @else
                                <span style="color: #9ca3af;">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center avg-column" style="vertical-align: middle; padding: 10px 6px;">
                        @if ($kpi['average'] !== null)
                            <span
                                style="font-size: 11pt; color: #15803d; font-weight: bold;">{{ number_format($kpi['average'], 2) }}</span>
                        @else
                            <span style="color: #9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($detail['months']) + 2 }}" class="text-center text-muted"
                        style="padding: 20px;">Belum ada data KPI
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($detail['appraisal'])
        <h2>Komentar</h2>

        <div class="comment-section">
            <div class="comment-title">Team Leader</div>
            @if ($detail['appraisal']->comment_teamleader)
                <div class="comment-content">{{ $detail['appraisal']->comment_teamleader }}</div>
            @else
                <div class="comment-empty">Belum ada komentar dari Team Leader</div>
            @endif
        </div>

        <div class="comment-section">
            <div class="comment-title">HRD</div>
            @if ($detail['appraisal']->comment_hrd)
                <div class="comment-content">{{ $detail['appraisal']->comment_hrd }}</div>
            @else
                <div class="comment-empty">Belum ada komentar dari HRD</div>
            @endif
        </div>

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
        </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem KPI App.</p>
    </div>
</body>

</html>
