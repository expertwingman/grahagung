<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Team View</h1>
        <p class="text-sm text-gray-400 mt-1">Monitor performa dan aktivitas seluruh staff</p>
    </div>

    {{-- Tab --}}
    <div class="flex gap-2 mb-6">
        <button onclick="switchTab('per-staff')" id="tab-per-staff"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white">
            👤 Per Staff
        </button>
        <button onclick="switchTab('overview')" id="tab-overview"
            class="px-4 py-2 rounded-lg text-sm font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-50">
            📊 Overview Semua Staff
        </button>
    </div>

    {{-- TAB 1: Per Staff --}}
    <div id="panel-per-staff">
        <div class="flex gap-6">

            {{-- Sidebar Staff List --}}
            <div class="w-56 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-3 border-b bg-gray-50">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Daftar Staff</p>
                    </div>
                    @forelse($staffList as $staff)
                    <a href="{{ route('team.index', ['staff_id' => $staff->id]) }}"
                        class="flex items-center gap-3 px-3 py-3 border-b hover:bg-gray-50 transition-colors {{ $selectedStaffId == $staff->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $staff->name }}</p>
                            <p class="text-xs text-gray-400">{{ $staff->leads()->count() }} leads</p>
                        </div>
                    </a>
                    @empty
                    <div class="p-4 text-center text-gray-400 text-sm">Belum ada staff</div>
                    @endforelse
                </div>
            </div>

            {{-- Detail Staff --}}
            <div class="flex-1">
                @if($selectedStaff)

                {{-- Header Staff --}}
                <div class="bg-white rounded-lg shadow-sm p-5 mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($selectedStaff->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">{{ $selectedStaff->name }}</h2>
                            <p class="text-sm text-gray-400">{{ $selectedStaff->email }}</p>
                            @if($selectedStaff->manager)
                                <p class="text-xs text-gray-400">Atasan: {{ $selectedStaff->manager->name }}</p>
                            @endif
                        </div>
                        <div class="ml-auto grid grid-cols-4 gap-3">
                            <div class="text-center bg-blue-50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-blue-600">{{ $staffStats['total_leads'] }}</p>
                                <p class="text-xs text-gray-500">Total Leads</p>
                            </div>
                            <div class="text-center bg-green-50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-green-600">{{ $staffStats['won_leads'] }}</p>
                                <p class="text-xs text-gray-500">Won</p>
                            </div>
                            <div class="text-center bg-yellow-50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-yellow-600">{{ $staffStats['conversion'] }}%</p>
                                <p class="text-xs text-gray-500">Konversi</p>
                            </div>
                            <div class="text-center bg-purple-50 rounded-lg p-3">
                                <p class="text-lg font-bold text-purple-600">Rp{{ number_format($staffStats['pipeline_value']/1000000, 1) }}jt</p>
                                <p class="text-xs text-gray-500">Pipeline</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Leads Staff --}}
                <div class="bg-white rounded-lg shadow-sm mb-4">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700">Leads ({{ $staffLeads->count() }})</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama</th>
                                <th class="text-left px-4 py-3 text-gray-600 font-medium">Perusahaan</th>
                                <th class="text-left px-4 py-3 text-gray-600 font-medium">Produk</th>
                                <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                                <th class="text-right px-4 py-3 text-gray-600 font-medium">Nilai</th>
                                <th class="text-center px-4 py-3 text-gray-600 font-medium">Tgl Input</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($staffLeads as $lead)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $lead->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $lead->company ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($lead->product)
                                        <span class="bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded text-xs">{{ $lead->product->name }}</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $lead->statusColor() }}">
                                        {{ $lead->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    {{ $lead->value ? 'Rp '.number_format($lead->value/1000000,1).'jt' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-400 text-xs">
                                    {{ $lead->created_at->format('d M Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada leads</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pipeline & Aktivitas --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold text-gray-700">Pipeline ({{ $staffPipelines->count() }})</h3>
                        </div>
                        <div class="divide-y">
                            @forelse($staffPipelines->take(5) as $pipeline)
                            <div class="px-4 py-3 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $pipeline->lead->name ?? '-' }}</p>
                                    <span class="text-xs text-gray-400">{{ ucfirst($pipeline->stage) }}</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $pipeline->value ? 'Rp '.number_format($pipeline->value/1000000,1).'jt' : '-' }}
                                </p>
                            </div>
                            @empty
                            <div class="p-4 text-center text-gray-400 text-sm">Belum ada pipeline</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold text-gray-700">Aktivitas Terbaru</h3>
                        </div>
                        <div class="divide-y">
                            @forelse($staffActivities->take(5) as $activity)
                            @php
                                $icons = ['call'=>'📞','meeting'=>'🤝','email'=>'📧','whatsapp'=>'💬','follow_up'=>'🔔','note'=>'📝'];
                            @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-start gap-2">
                                    <span class="text-sm">{{ $icons[$activity->type] ?? '📌' }}</span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $activity->title }}</p>
                                        <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="ml-auto text-xs {{ $activity->status === 'done' ? 'text-green-500' : 'text-yellow-500' }}">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="p-4 text-center text-gray-400 text-sm">Belum ada aktivitas</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @else
                <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-400">
                    Pilih staff di sebelah kiri untuk melihat detail pekerjaan mereka
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB 2: Overview Semua Staff --}}
    <div id="panel-overview" class="hidden">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-semibold text-gray-700">Perbandingan Performa Semua Staff</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600 font-medium">Staff</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium">Total Leads</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium">New</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium">Won</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium">Konversi</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-medium">Pipeline Aktif</th>
                        <th class="text-right px-4 py-3 text-gray-600 font-medium">Total Won</th>
                        <th class="text-center px-4 py-3 text-gray-600 font-medium">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($allStaffData as $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($data['staff']->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $data['staff']->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $data['staff']->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $data['total_leads'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ $data['new_leads'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">{{ $data['won_leads'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center gap-2 justify-center">
                                <div class="w-16 bg-gray-100 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width:{{ $data['conversion'] }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $data['conversion'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            Rp {{ number_format($data['pipeline_value']/1000000, 1) }}jt
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">
                            Rp {{ number_format($data['won_value']/1000000, 1) }}jt
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('team.index', ['staff_id' => $data['staff']->id]) }}"
                                onclick="switchTab('per-staff')"
                                class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded text-xs font-medium hover:bg-blue-100">
                                Lihat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada staff</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function switchTab(tab) {
        document.getElementById('panel-per-staff').classList.add('hidden');
        document.getElementById('panel-overview').classList.add('hidden');
        document.getElementById('tab-per-staff').className = 'px-4 py-2 rounded-lg text-sm font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-50';
        document.getElementById('tab-overview').className = 'px-4 py-2 rounded-lg text-sm font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-50';

        document.getElementById('panel-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).className = 'px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white';
    }
    </script>
</x-app-layout>
