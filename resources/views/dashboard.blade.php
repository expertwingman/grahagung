<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-400 mt-1">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Leads</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalLeads }}</p>
            <p class="text-xs text-blue-500 mt-1">{{ $newLeads }} baru bulan ini</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Pipeline Aktif</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $activePipelines }}</p>
            <p class="text-xs text-yellow-500 mt-1">Rp {{ number_format($pipelineValue/1000000, 1) }}jt total nilai</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Proyek Berjalan</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $activeProjects }}</p>
            <p class="text-xs text-purple-500 mt-1">{{ $completedProjects }} selesai</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Deal Won</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $wonDeals }}</p>
            <p class="text-xs text-green-500 mt-1">Rp {{ number_format($wonValue/1000000, 1) }}jt total</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-3 gap-6 mb-6">
        {{-- Leads per Bulan --}}
        <div class="col-span-2 bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold text-gray-700 mb-4">Leads per Bulan</h2>
            <canvas id="leadsChart" height="120"></canvas>
        </div>

        {{-- Leads per Sumber --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold text-gray-700 mb-4">Leads per Sumber</h2>
            <canvas id="sourceChart" height="120"></canvas>
        </div>
    </div>

    {{-- Panel: Lead Perlu Follow Up --}}
    @if($followUpCount > 0)
    <div class="bg-white rounded-lg shadow-sm mb-6 border-l-4 border-orange-400">
        <div class="flex items-center justify-between p-4 border-b">
            <div class="flex items-center gap-2">
                <span class="text-lg">⏰</span>
                <h2 class="font-semibold text-gray-700">Perlu Follow Up</h2>
                <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $followUpCount }}</span>
            </div>
            <a href="{{ route('leads.index', ['needs_followup' => 1]) }}" class="text-xs text-orange-600 hover:underline font-medium">Lihat semua</a>
        </div>
        <div class="divide-y">
            @foreach($followUpLeads as $lead)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-orange-50">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-800 truncate">{{ $lead->name }}</span>
                            @if(str_starts_with($lead->name, 'Calon '))
                                <span class="px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-600 font-medium"
                                      title="Nama belum dilengkapi">! Nama</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->statusColor() }}">{{ $lead->statusLabel() }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $lead->assignedTo->name ?? '-' }} · {{ $lead->product->name ?? 'Tanpa produk' }}
                            @if($lead->follow_up_date)
                                · FU terakhir {{ \Carbon\Carbon::parse($lead->follow_up_date)->diffForHumans() }}
                            @else
                                · <span class="text-orange-600">belum pernah di-FU</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-3">
                        @if($lead->wa_phone)
                            <a href="https://wa.me/{{ $lead->wa_phone }}" target="_blank"
                               class="text-xs bg-green-500 text-white px-2.5 py-1 rounded hover:bg-green-600">WhatsApp</a>
                        @endif
                        <form method="POST" action="{{ route('leads.followed-up', $lead) }}">
                            @csrf
                            <button type="submit" title="Tandai sudah di-follow up hari ini"
                                class="text-xs border border-green-300 text-green-700 px-2.5 py-1 rounded hover:bg-green-50">✓ Sudah FU</button>
                        </form>
                        <a href="{{ route('leads.show', $lead) }}"
                           class="text-xs border border-gray-300 text-gray-600 px-2.5 py-1 rounded hover:bg-gray-50">Lihat</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-6">
        {{-- Leads Terbaru --}}
        <div class="col-span-2 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="font-semibold text-gray-700">Leads Terbaru</h2>
                <a href="{{ route('leads.index') }}" class="text-xs text-blue-500 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y">
                @forelse($recentLeads as $lead)
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $lead->name }}</p>
                        <p class="text-xs text-gray-400">{{ $lead->company ?? $lead->source ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $lead->statusColor() }}">
                            {{ $lead->statusLabel() }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $lead->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-400 text-sm">
                    Belum ada leads.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Aktivitas Mendatang --}}
        <div class="bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="font-semibold text-gray-700">Aktivitas Mendatang</h2>
                <a href="{{ route('activities.index') }}" class="text-xs text-blue-500 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y">
                @forelse($upcomingActivities as $activity)
                @php
                    $typeIcons = [
                        'call'      => '📞',
                        'meeting'   => '🤝',
                        'email'     => '📧',
                        'whatsapp'  => '💬',
                        'follow_up' => '🔔',
                        'note'      => '📝',
                    ];
                @endphp
                <div class="px-4 py-3">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">{{ $typeIcons[$activity->type] ?? '📌' }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $activity->title }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $activity->scheduled_at ? $activity->scheduled_at->format('d M, H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-400 text-sm">
                    Tidak ada aktivitas mendatang
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // Data dari PHP
        const leadsPerMonth = @json($leadsPerMonth);
        const leadsPerSource = @json($leadsPerSource);

        // Chart 1: Leads per Bulan (Bar)
        // Chart 1: Leads per Bulan (Line)
        const ctx1 = document.getElementById('leadsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: leadsPerMonth.map(d => d.month),
                datasets: [{
                    label: 'Jumlah Leads',
                    data: leadsPerMonth.map(d => d.total),
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointRadius: 4,
                    tension: 0,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Chart 2: Leads per Sumber (Donut)
        const ctx2 = document.getElementById('sourceChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: leadsPerSource.map(d => d.source ?? 'Lainnya'),
                datasets: [{
                    data: leadsPerSource.map(d => d.total),
                    backgroundColor: [
                        '#3B82F6','#F59E0B','#8B5CF6','#10B981','#EF4444','#EC4899','#6366F1'
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
            }
        });

    });
    </script>
</x-app-layout>
