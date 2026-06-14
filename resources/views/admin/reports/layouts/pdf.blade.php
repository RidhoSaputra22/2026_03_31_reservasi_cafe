<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #2f241c;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .page {
            padding: 26px 28px 30px;
        }

        .header {
            border-bottom: 2px solid #6f4b32;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .eyebrow {
            color: #8a623d;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.18em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        h1 {
            font-size: 24px;
            margin: 0 0 6px;
            color: #271b15;
        }

        .subtitle {
            margin: 0;
            color: #5f4d40;
            font-size: 12px;
        }

        .meta,
        .filters,
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .meta td,
        .filters td {
            padding: 8px 10px;
            border: 1px solid #e6ddd3;
            vertical-align: top;
        }

        .meta .label,
        .filters .label {
            width: 18%;
            color: #7a6657;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .summary td {
            width: 25%;
            padding-right: 10px;
            vertical-align: top;
        }

        .summary-card {
            border: 1px solid #dbcab6;
            background: #fbf7f0;
            border-radius: 8px;
            padding: 12px;
        }

        .summary-label {
            color: #7a6657;
            font-size: 10px;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .summary-value {
            color: #271b15;
            font-size: 16px;
            font-weight: 700;
        }

        .section-title {
            margin: 22px 0 10px;
            color: #271b15;
            font-size: 14px;
            font-weight: 700;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table tr {
            page-break-inside: avoid;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #eadfd4;
            padding: 8px 9px;
            vertical-align: top;
        }

        .report-table th {
            background: #6f4b32;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .report-table tbody tr:nth-child(even) {
            background: #fcfaf7;
        }

        .muted {
            color: #7a6657;
            font-size: 10px;
        }

        .empty-state {
            border: 1px dashed #d7c0a0;
            padding: 18px;
            border-radius: 12px;
            color: #7a6657;
            background: #fbf7f0;
        }

        .footer {
            margin-top: 20px;
            color: #8a796c;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <div class="eyebrow">AMIKOSPACE Admin Report</div>
            <h1>{{ $title }}</h1>
            <p class="subtitle">{{ $subtitle }}</p>

            <table class="meta">
                <tr>
                    <td class="label">Cafe</td>
                    <td>{{ $profile?->name ?? 'AMIKOSPACE' }}</td>
                    <td class="label">Diekspor Oleh</td>
                    <td>{{ $exportedBy }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td>{{ $profile?->address ?? '-' }}</td>
                    <td class="label">Waktu Export</td>
                    <td>{{ $exportedAt->translatedFormat('d M Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <table class="filters">
            @foreach ($filters as $filter)
                <tr>
                    <td class="label">{{ $filter['label'] }}</td>
                    <td>{{ $filter['value'] }}</td>
                </tr>
            @endforeach
        </table>

        @if (! empty($summaryStats))
            <table class="summary">
                <tr>
                    @foreach ($summaryStats as $summary)
                        <td>
                            <div class="summary-card">
                                <div class="summary-label">{{ $summary['label'] }}</div>
                                <div class="summary-value">{{ $summary['value'] }}</div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif

        @yield('report-content')

        <div class="footer">
            Dokumen ini dibuat otomatis dari panel admin AMIKOSPACE.
        </div>
    </div>
</body>

</html>
