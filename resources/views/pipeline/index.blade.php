<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pipeline</h1>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            + Tambah Deal
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach($stages as $key => $label)
        @php
            $headerColors = [
                'new'         => 'bg-blue-500',
                'contacted'   => 'bg-yellow-500',
                'survey'      => 'bg-purple-500',
                'proposal'    => 'bg-orange-500',
                'negotiation' => 'bg-pink-500',
                'won'         => 'bg-green-500',
                'lost'        => 'bg-red-500',
            ];
            $stageDeals = $pipelines[$key] ?? collect();
            $totalValue = $stageDeals->sum('value');
        @endphp
        <div class="flex-shrink-0 w-64">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $headerColors[$key] }}"></span>
                    <span class="text-sm font-semibold text-gray-700">{{ $label }}</span>
                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $stageDeals->count() }}</span>
                </div>
                @if($totalValue > 0)
                    <span class="text-xs text-gray-400">Rp {{ number_format($totalValue/1000000, 1) }}jt</span>
                @endif
            </div>
            <div class="space-y-2 min-h-24">
                @forelse($stageDeals as $deal)
                <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <p class="text-sm font-medium text-gray-800">{{ $deal->lead->name ?? '-' }}</p>
                        <form method="POST" action="{{ route('pipeline.destroy', $deal) }}" onsubmit="return confirm('Hapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-300 hover:text-red-400 text-xs">x</button>
                        </form>
                    </div>
                    @if($deal->lead->company)
                        <p class="text-xs text-gray-400 mb-2">{{ $deal->lead->company }}</p>
                    @endif
                    @if($deal->value)
                        <p class="text-sm font-semibold text-green-600">Rp {{ number_format($deal->value, 0, ',', '.') }}</p>
                    @endif
                    @if($deal->expected_close_date)
                        <p class="text-xs text-gray-400 mt-1">Tutup: {{ $deal->expected_close_date->format('d M Y') }}</p>
                    @endif
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <select onchange="moveStage({{ $deal->id }}, this.value)"
                            class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-gray-600">
                            @foreach($stages as $sk => $sl)
                                <option value="{{ $sk }}" {{ $deal->stage == $sk ? 'selected' : '' }}>{{ $sl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @empty
                <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-300">Kosong</p>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    <div id="modal-add" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Tambah Deal</h2>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">x</button>
            </div>
            <form method="POST" action="{{ route('pipeline.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lead</label>
                        <select name="lead_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Lead --</option>
                            @foreach(\App\Models\Lead::orderBy('name')->get() as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? ' - '.$lead->company : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                        <select name="stage" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @foreach($stages as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Deal (Rp)</label>
                        <input type="number" name="value" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Tutup</label>
                        <input type="date" name="expected_close_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium flex-1">Tambah</button>
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm flex-1">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function moveStage(id, stage) {
        fetch('/pipeline/' + id + '/stage', {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ stage: stage })
        }).then(() => window.location.reload());
    }
    </script>
</x-app-layout>
