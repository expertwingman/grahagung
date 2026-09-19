<x-app-layout>
    {{-- Header & Filter --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
            <p class="text-sm text-gray-400 mt-1">Laporan performa penjualan GAK CRM</p>
        </div>
        <div class="flex gap-3 items-center">
            <form method="GET" action="{{ route('reports') }}" class="flex gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                    Tampilkan
                </button>
            </form>
            <a href="{{ route('reports.print', ['bulan' => $bulan]) }}" target="_blank"
            class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-900 flex items-center gap-2">
                🖨️ Print / PDF
            </a>
            <a href="{{ route('reports.export', ['bulan' => $bulan]) }}"
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 flex items-center gap-2">
                📥 Export Excel
            </a>
        </div>
    </div>

    {{-- Print Area --}}
    <div id="print-area">

        {{-- Header Laporan --}}
        <div class="bg-gradient-to-r from-gray-900 to-gray-700 text-white rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">GAK CRM</h2>
                    <p class="text-gray-300 text-sm">AI Marketing Suite</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold">Laporan Bulanan</p>
                    <p class="text-gray-300 text-sm">{{ $periode->locale('id')->isoFormat('MMMM Y') }}</p>
                    <p class="text-gray-400 text-xs mt-1">Dicetak: {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Ringkasan Eksekutif --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded"></span>
                Ringkasan Eksekutif
            </h3>
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-lg p-4 shadow-sm border-t-4 border-blue-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Leads</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalLeads }}</p>
                    <p class="text-xs text-blue-500 mt-1">{{ $newLeads }} baru bulan ini</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border-t-4 border-green-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Deal Won</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $wonLeads }}</p>
                    <p class="text-xs text-green-500 mt-1">Rp {{ number_format($wonValue/1000000, 1) }}jt total nilai</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border-t-4 border-yellow-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Conversion Rate</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $conversionRate }}%</p>
                    <p class="text-xs text-yellow-500 mt-1">{{ $lostLeads }} leads hilang</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border-t-4 border-purple-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Pipeline</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalPipelineValue/1000000, 1) }}jt</p>
                    <p class="text-xs text-purple-500 mt-1">{{ $activeProjects }} proyek aktif</p>
                </div>
            </div>
        </div>

        {{-- Grafik & Sumber --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-500 rounded"></span>
                    Trend Leads 6 Bulan
                </h3>
                <canvas id="trendChart" height="150"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-1 h-4 bg-green-500 rounded"></span>
                    Leads per Sumber
                </h3>
                <div class="space-y-2 mt-2">
                    @foreach($leadsBySource as $item)
                    @php $pct = $totalLeads > 0 ? round(($item->total/$totalLeads)*100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600 font-medium">{{ ucfirst($item->source ?? 'Lainnya') }}</span>
                            <span class="text-gray-800 font-semibold">{{ $item->total }} leads ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tabel Leads per Status --}}
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-4 border-b flex items-center gap-2">
                <span class="w-1 h-5 bg-purple-500 rounded"></span>
                <h3 class="font-bold text-gray-800">Breakdown Leads per Status</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-semibold">Status</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-semibold">Jumlah Leads</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-semibold">Persentase</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-semibold">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($leadsByStatus as $item)
                    @php
                        $pct = $totalLeads > 0 ? round(($item->total/$totalLeads)*100) : 0;
                        $statusColor = \App\Models\Lead::STATUS_COLORS[$item->status] ?? 'bg-gray-100 text-gray-700';
                        $statusLabel = \App\Models\Lead::STATUSES[$item->status] ?? $item->status;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $item->total }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            {{ $item->nilai ? 'Rp '.number_format($item->nilai/1000000, 1).'jt' : '-' }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-4 py-3 text-gray-800">Total</td>
                        <td class="px-4 py-3 text-center text-gray-800">{{ $totalLeads }}</td>
                        <td class="px-4 py-3 text-center text-gray-800">100%</td>
                        <td class="px-4 py-3 text-right text-gray-800">Rp {{ number_format($leadsByStatus->sum('nilai')/1000000, 1) }}jt</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Tabel Pipeline Aktif --}}
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-4 border-b flex items-center gap-2">
                <span class="w-1 h-5 bg-yellow-500 rounded"></span>
                <h3 class="font-bold text-gray-800">Pipeline Aktif (Top 10)</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-semibold">Lead</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-semibold">Stage</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-semibold">Nilai Deal</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-semibold">Target Tutup</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activePipelines as $pipeline)
                    @php
                        $stageColors = [
                            'new'=>'bg-blue-100 text-blue-700',
                            'contacted'=>'bg-yellow-100 text-yellow-700',
                            'survey'=>'bg-purple-100 text-purple-700',
                            'proposal'=>'bg-orange-100 text-orange-700',
                            'negotiation'=>'bg-pink-100 text-pink-700',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $pipeline->lead->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $pipeline->lead->company ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $stageColors[$pipeline->stage] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($pipeline->stage) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            {{ $pipeline->value ? 'Rp '.number_format($pipeline->value, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $pipeline->expected_close_date ? $pipeline->expected_close_date->format('d M Y') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Tidak ada pipeline aktif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tabel Proyek --}}
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-4 border-b flex items-center gap-2">
                <span class="w-1 h-5 bg-green-500 rounded"></span>
                <h3 class="font-bold text-gray-800">Status Proyek</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-semibold">Nama Proyek</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-semibold">Status</th>
                        <th class="text-left px-4 py-3 text-gray-600 font-semibold">Prioritas</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-semibold">Progress</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-semibold">Nilai</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-semibold">Deadline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($projects as $project)
                    @php
                        $statusColors = [
                            'planning'=>'bg-blue-100 text-blue-700',
                            'in_progress'=>'bg-yellow-100 text-yellow-700',
                            'on_hold'=>'bg-gray-100 text-gray-700',
                            'completed'=>'bg-green-100 text-green-700',
                            'cancelled'=>'bg-red-100 text-red-700',
                        ];
                        $priorityColors = [
                            'low'=>'bg-gray-100 text-gray-600',
                            'medium'=>'bg-yellow-100 text-yellow-700',
                            'high'=>'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $project->name }}</p>
                            <p class="text-xs text-gray-400">{{ $project->lead->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$project->status] ?? '' }}">
                                {{ ucfirst(str_replace('_',' ',$project->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $priorityColors[$project->priority] ?? '' }}">
                                {{ ucfirst($project->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width:{{ $project->progress }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ number_format($project->progress, 0) }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            {{ $project->value ? 'Rp '.number_format($project->value/1000000,1).'jt' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Tidak ada proyek</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Laporan --}}
        <div class="bg-gray-50 rounded-lg p-4 text-center text-xs text-gray-400 border">
            <p class="font-medium text-gray-600">GAK CRM — AI Marketing Suite</p>
            <p class="mt-1">Laporan ini dibuat secara otomatis oleh sistem pada {{ now()->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB</p>
            <p class="mt-1">Dokumen ini bersifat rahasia dan hanya untuk keperluan internal perusahaan</p>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const leadsPerMonth = @json($leadsPerMonth);
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: leadsPerMonth.map(d => d.month),
                datasets: [{
                    label: 'Leads',
                    data: leadsPerMonth.map(d => d.total),
                    borderColor: 'rgba(59,130,246,1)',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 3,
                    tension: 0,
                    fill: true,
                    pointBackgroundColor: 'rgba(59,130,246,1)',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    });
    </script>

    <style>
    @media print {
        .sidebar, nav, .topbar, button, form { display: none !important; }
        #print-area { padding: 0; }
        body { background: white; }
        .shadow-sm { box-shadow: none; }
    }
    </style>

</x-app-layout>
