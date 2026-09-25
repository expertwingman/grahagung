{{-- Batang kontak per sales minggu ini --}}
<div class="bg-white rounded-xl shadow-sm p-5">
    <h2 class="font-bold text-gray-700 mb-1">Aktivitas tim minggu ini</h2>
    <p class="text-xs text-gray-400 mb-4">Follow-up + kunjungan per orang</p>
    <div class="space-y-2.5">
        @php
            $allIds = $staff->pluck('id');
            $maxKontak = max(1, $allIds->map(fn($id) => ($kontakPerSales[$id] ?? 0) + ($kunjunganPerSales[$id] ?? 0))->max());
        @endphp
        @foreach($staff as $s)
            @php
                $fu = $kontakPerSales[$s->id] ?? 0;
                $kj = $kunjunganPerSales[$s->id] ?? 0;
                $tot = $fu + $kj;
                $pct = max(4, $tot / $maxKontak * 100);
            @endphp
            <div class="flex items-center gap-3">
                <span class="w-28 text-xs text-gray-600 text-right shrink-0 truncate" title="{{ $s->name }}">{{ Str::before($s->name, ' ') }}</span>
                <div class="flex-1 h-7 bg-gray-100 rounded-lg overflow-hidden flex">
                    @if($fu > 0)
                    <div class="h-full bg-blue-500 flex items-center justify-center text-[10px] font-bold text-white" style="width: {{ $fu / $maxKontak * 100 }}%">{{ $fu }}</div>
                    @endif
                    @if($kj > 0)
                    <div class="h-full bg-emerald-500 flex items-center justify-center text-[10px] font-bold text-white" style="width: {{ $kj / $maxKontak * 100 }}%">{{ $kj }}</div>
                    @endif
                </div>
                <span class="text-xs font-bold text-gray-700 w-8 text-right">{{ $tot }}</span>
            </div>
        @endforeach
        <div class="flex items-center gap-4 mt-2 text-[10px] text-gray-400">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> Follow-up</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500"></span> Kunjungan</span>
        </div>
    </div>
</div>
