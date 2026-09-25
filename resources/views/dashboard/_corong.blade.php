{{-- Corong lead: batang bertingkat --}}
<div class="bg-white rounded-xl shadow-sm p-5">
    <h2 class="font-bold text-gray-700 mb-4">{{ $judul ?? 'Corong lead' }}</h2>
    <div class="space-y-2">
        @php $max = max(1, collect($corong)->max('total')); @endphp
        @foreach($corong as $c)
        <div class="flex items-center gap-3">
            <span class="w-28 text-xs text-gray-600 text-right shrink-0">{{ $c['label'] }}</span>
            <div class="flex-1 h-7 bg-gray-100 rounded-lg overflow-hidden">
                <div class="h-full rounded-lg bg-blue-500 flex items-center pl-2 text-xs font-bold text-white transition-all"
                     style="width: {{ max(8, $c['total'] / $max * 100) }}%">
                    {{ $c['total'] }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
