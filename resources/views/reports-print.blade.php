<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan GAK CRM - {{ $periode->locale('id')->isoFormat('MMMM Y') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: white; padding: 20px; }
        .header { background: #1f2937; color: white; padding: 20px 24px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 20px; font-weight: 800; }
        .header p { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .header-right { text-align: right; }
        .header-right h2 { font-size: 14px; font-weight: 600; }
        .header-right p { font-size: 11px; color: #9ca3af; }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
        .kpi-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; border-top: 4px solid; }
        .kpi-card.blue { border-top-color: #3b82f6; }
        .kpi-card.green { border-top-color: #10b981; }
        .kpi-card.yellow { border-top-color: #f59e0b; }
        .kpi-card.purple { border-top-color: #8b5cf6; }
        .kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 4px; }
        .kpi-value { font-size: 22px; font-weight: 800; color: #111827; }
        .kpi-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 10px; padding-left: 8px; border-left: 3px solid #3b82f6; }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #f9fafb; padding: 8px 10px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; }
        td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; }

        /* Status lead GAK */
        .badge-no_respon   { background: #f3f4f6; color: #374151; }
        .badge-respon      { background: #dbeafe; color: #1d4ed8; }
        .badge-kirim_pl    { background: #ede9fe; color: #5b21b6; }
        .badge-survey      { background: #fef3c7; color: #92400e; }
        .badge-utj         { background: #ffedd5; color: #9a3412; }
        .badge-closing     { background: #dcfce7; color: #14532d; }
        .badge-batal       { background: #fee2e2; color: #7f1d1d; }

        /* Status pipeline stage (beda dari lead status) */
        .badge-new         { background: #f3f4f6; color: #374151; }
        .badge-contacted   { background: #dbeafe; color: #1d4ed8; }
        .badge-proposal    { background: #ffedd5; color: #9a3412; }
        .badge-negotiation { background: #fce7f3; color: #9d174d; }
        .badge-won         { background: #dcfce7; color: #14532d; }
        .badge-lost        { background: #fee2e2; color: #7f1d1d; }

        /* Status proyek & prioritas */
        .badge-planning    { background: #dbeafe; color: #1d4ed8; }
        .badge-in_progress { background: #fef3c7; color: #92400e; }
        .badge-completed   { background: #dcfce7; color: #14532d; }
        .badge-cancelled   { background: #fee2e2; color: #7f1d1d; }
        .badge-high        { background: #fee2e2; color: #7f1d1d; }
        .badge-medium      { background: #fef3c7; color: #92400e; }
        .badge-low         { background: #f3f4f6; color: #374151; }

        .progress-wrap { display: flex; align-items: center; gap: 6px; }
        .progress-bar { flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
        .progress-fill { height: 100%; background: #3b82f6; border-radius: 3px; }
        .source-item { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
        .source-bar-wrap { flex: 1; margin: 0 10px; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
        .source-bar-fill { height: 100%; background: #3b82f6; border-radius: 3px; }
        .footer { text-align: center; padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; margin-top: 20px; }
        .footer p { font-size: 10px; color: #6b7280; margin-bottom: 2px; }
        .total-row { background: #f9fafb; font-weight: 700; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: A4; }
        }
    </style>
</head>
<body>

    @php
        // Label status lead GAK untuk halaman print (tidak bisa pakai method model di sini)
        $statusLabels = \App\Models\Lead::STATUSES;
    @endphp

    {{-- Tombol Print (hilang saat print) --}}
    <div class="no-print" style="text-align:right; margin-bottom:16px;">
        <a href="{{ route('reports') }}" style="background:#f3f4f6; color:#374151; padding:8px 16px; border-radius:6px; text-decoration:none; font-size:12px; margin-right:8px;">← Kembali</a>
        <button onclick="window.print()" style="background:#1f2937; color:white; padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:12px;">🖨️ Print / Save PDF</button>
    </div>

    {{-- Header --}}
    <div class="header">
        <div>
            <h1>GAK CRM</h1>
            <p>AI Marketing Suite</p>
        </div>
        <div class="header-right">
            <h2>Laporan Bulanan</h2>
            <p>{{ $periode->locale('id')->isoFormat('MMMM Y') }}</p>
            <p>Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
        </div>
    </div>

    {{-- KPI --}}
    <div class="section">
        <div class="section-title">Ringkasan Eksekutif</div>
        <div class="kpi-grid">
            <div class="kpi-card blue">
                <div class="kpi-label">Total Leads</div>
                <div class="kpi-value">{{ $totalLeads }}</div>
                <div class="kpi-sub">{{ $newLeads }} baru bulan ini</div>
            </div>
            <div class="kpi-card green">
                <div class="kpi-label">Closing</div>
                <div class="kpi-value">{{ $wonLeads }}</div>
                <div class="kpi-sub">Rp {{ number_format($wonValue/1000000, 1) }}jt total nilai</div>
            </div>
            <div class="kpi-card yellow">
                <div class="kpi-label">Conversion Rate</div>
                <div class="kpi-value">{{ $conversionRate }}%</div>
                <div class="kpi-sub">{{ $lostLeads }} leads batal</div>
            </div>
            <div class="kpi-card purple">
                <div class="kpi-label">Total Pipeline</div>
                <div class="kpi-value">Rp {{ number_format($totalPipelineValue/1000000, 0) }}jt</div>
                <div class="kpi-sub">{{ $activeProjects }} proyek aktif</div>
            </div>
        </div>
    </div>

    {{-- Leads per Sumber --}}
    <div class="two-col">
        <div class="section">
            <div class="section-title">Leads per Sumber</div>
            @foreach($leadsBySource as $item)
            @php $pct = $totalLeads > 0 ? round(($item->total/$totalLeads)*100) : 0; @endphp
            <div class="source-item">
                <span style="width:100px; font-weight:500;">{{ $item->source ?? 'Lainnya' }}</span>
                <div class="source-bar-wrap"><div class="source-bar-fill" style="width:{{ $pct }}%"></div></div>
                <span style="width:80px; text-align:right; color:#6b7280;">{{ $item->total }} ({{ $pct }}%)</span>
            </div>
            @endforeach
        </div>

        {{-- Leads per Status Summary --}}
        <div class="section">
            <div class="section-title">Ringkasan Status Leads</div>
            @foreach($leadsByStatus as $item)
            @php $pct = $totalLeads > 0 ? round(($item->total/$totalLeads)*100) : 0; @endphp
            <div class="source-item">
                <span class="badge badge-{{ $item->status }}" style="width:90px; text-align:center;">
                    {{ $statusLabels[$item->status] ?? $item->status }}
                </span>
                <div class="source-bar-wrap"><div class="source-bar-fill" style="width:{{ $pct }}%"></div></div>
                <span style="width:80px; text-align:right; color:#6b7280;">{{ $item->total }} ({{ $pct }}%)</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tabel Leads per Status --}}
    <div class="section">
        <div class="section-title">Breakdown Leads per Status</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="text-align:center">Jumlah</th>
                    <th>Persentase</th>
                    <th style="text-align:right">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leadsByStatus as $item)
                @php $pct = $totalLeads > 0 ? round(($item->total/$totalLeads)*100) : 0; @endphp
                <tr>
                    <td>
                        <span class="badge badge-{{ $item->status }}">
                            {{ $statusLabels[$item->status] ?? $item->status }}
                        </span>
                    </td>
                    <td style="text-align:center; font-weight:600;">{{ $item->total }}</td>
                    <td>
                        <div class="progress-wrap">
                            <div class="progress-bar"><div class="progress-fill" style="width:{{ $pct }}%"></div></div>
                            <span style="width:30px; color:#6b7280;">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td style="text-align:right;">{{ $item->nilai ? 'Rp '.number_format($item->nilai/1000000,1).'jt' : '-' }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total</td>
                    <td style="text-align:center;">{{ $totalLeads }}</td>
                    <td>100%</td>
                    <td style="text-align:right;">Rp {{ number_format($leadsByStatus->sum('nilai')/1000000,1) }}jt</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pipeline Aktif --}}
    <div class="section">
        <div class="section-title">Pipeline Aktif (Top 10)</div>
        <table>
            <thead>
                <tr>
                    <th>Lead</th>
                    <th>Perusahaan</th>
                    <th>Stage</th>
                    <th style="text-align:right">Nilai Deal</th>
                    <th style="text-align:center">Target Tutup</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activePipelines as $p)
                <tr>
                    <td style="font-weight:500;">{{ $p->lead->name ?? '-' }}</td>
                    <td style="color:#6b7280;">{{ $p->lead->company ?? '-' }}</td>
                    <td><span class="badge badge-{{ $p->stage }}">{{ ucfirst($p->stage) }}</span></td>
                    <td style="text-align:right; font-weight:600;">{{ $p->value ? 'Rp '.number_format($p->value,0,',','.') : '-' }}</td>
                    <td style="text-align:center; color:#6b7280;">{{ $p->expected_close_date ? $p->expected_close_date->format('d M Y') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:#9ca3af;">Tidak ada pipeline aktif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Status Proyek --}}
    <div class="section">
        <div class="section-title">Status Proyek</div>
        <table>
            <thead>
                <tr>
                    <th>Nama Proyek</th>
                    <th>Lead</th>
                    <th>Status</th>
                    <th>Prioritas</th>
                    <th>Progress</th>
                    <th style="text-align:right">Nilai</th>
                    <th style="text-align:center">Deadline</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td style="font-weight:500;">{{ $project->name }}</td>
                    <td style="color:#6b7280;">{{ $project->lead->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $project->status }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span></td>
                    <td><span class="badge badge-{{ $project->priority }}">{{ ucfirst($project->priority) }}</span></td>
                    <td>
                        <div class="progress-wrap">
                            <div class="progress-bar"><div class="progress-fill" style="width:{{ $project->progress }}%"></div></div>
                            <span style="width:30px; color:#6b7280;">{{ number_format($project->progress,0) }}%</span>
                        </div>
                    </td>
                    <td style="text-align:right;">{{ $project->value ? 'Rp '.number_format($project->value/1000000,1).'jt' : '-' }}</td>
                    <td style="text-align:center; color:#6b7280;">{{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:#9ca3af;">Tidak ada proyek</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p style="font-weight:600; color:#374151;">GAK CRM — AI Marketing Suite</p>
        <p>Laporan dibuat otomatis pada {{ now()->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB</p>
        <p>Dokumen ini bersifat rahasia dan hanya untuk keperluan internal perusahaan</p>
    </div>

</body>
</html>
