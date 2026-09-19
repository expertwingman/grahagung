<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Analytics</h1>
        <p class="text-sm text-gray-400 mt-1">Ringkasan performa penjualan dan leads</p>
    </div>

    {{-- KPI Row --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Leads</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalLeads }}</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Nilai Pipeline</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalPipelineValue/1000000, 1) }}jt</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Conversion Rate</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $conversionRate }}%</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Rata-rata Nilai Deal</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($avgDealValue/1000000, 1) }}jt</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        {{-- Leads per Status --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold text-gray-700 mb-4">Leads per Status</h2>
            <canvas id="statusChart" height="200"></canvas>
        </div>

        {{-- Pipeline per Stage --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold text-gray-700 mb-4">Nilai Pipeline per Stage</h2>
            <canvas id="pipelineChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Top Sumber Leads --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold text-gray-700 mb-4">Top Sumber Leads</h2>
            <div class="space-y-3">
                @foreach($leadsPerSource as $item)
                @php
                    $pct = $totalLeads > 0 ? round(($item->total / $totalLeads) * 100) : 0;
                    $colors = ['referral'=>'bg-blue-500','instagram'=>'bg-pink-500','facebook'=>'bg-indigo-500','google'=>'bg-red-500','whatsapp'=>'bg-green-500','website'=>'bg-yellow-500','other'=>'bg-gray-500'];
                    $color = $colors[$item->source] ?? 'bg-gray-500';
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">{{ ucfirst($item->source ?? 'Lainnya') }}</span>
                        <span class="font-medium text-gray-800">{{ $item->total }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="{{ $color }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Proyek per Status --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold text-gray-700 mb-4">Proyek per Status</h2>
            <canvas id="projectChart" height="200"></canvas>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const statusData = @json($leadsPerStatus);
        const pipelineData = @json($pipelinePerStage);
        const projectData = @json($projectsPerStatus);

        // Chart: Leads per Status
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.map(d => d.status),
                datasets: [{
                    data: statusData.map(d => d.total),
                    backgroundColor: ['#3B82F6','#F59E0B','#8B5CF6','#F97316','#EC4899','#10B981','#EF4444'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
        });

        // Chart: Pipeline per Stage
        new Chart(document.getElementById('pipelineChart'), {
            type: 'bar',
            data: {
                labels: pipelineData.map(d => d.stage),
                datasets: [{
                    label: 'Nilai (jt)',
                    data: pipelineData.map(d => Math.round(d.total / 1000000)),
                    backgroundColor: ['#3B82F6','#F59E0B','#8B5CF6','#F97316','#10B981','#EF4444'],
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp '+v+'jt' } } }
            }
        });

        // Chart: Proyek per Status
        new Chart(document.getElementById('projectChart'), {
            type: 'pie',
            data: {
                labels: projectData.map(d => d.status),
                datasets: [{
                    data: projectData.map(d => d.total),
                    backgroundColor: ['#3B82F6','#F59E0B','#6B7280','#10B981','#EF4444'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
        });

    });
    </script>
</x-app-layout>
