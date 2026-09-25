{{-- Cold lead per sales --}}
@if(count($coldPerSales) > 0)
<div class="bg-white rounded-xl shadow-sm p-5">
    <h2 class="font-bold text-gray-700 mb-1">Cold lead per sales</h2>
    <p class="text-xs text-gray-400 mb-3">Diam 30+ hari — anggaran iklan yang menguap</p>
    <div class="space-y-2">
        @php $maxCold = max(1, collect($coldPerSales)->max('count')); @endphp
        @foreach($coldPerSales as $c)
        <div class="flex items-center gap-3">
            <span class="w-28 text-xs text-gray-600 text-right shrink-0 truncate">{{ Str::before($c['name'], ' ') }}</span>
            <div class="flex-1 h-6 bg-gray-100 rounded-lg overflow-hidden">
                <div class="h-full rounded-lg bg-orange-400 flex items-center pl-2 text-[10px] font-bold text-white"
                     style="width: {{ max(8, $c['count'] / $maxCold * 100) }}%">{{ $c['count'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
    <a href="{{ route('reaktivasi.index') }}" class="inline-block mt-3 text-xs font-semibold text-emerald-600 hover:underline">Buka Reaktivasi →</a>
</div>
@endif
