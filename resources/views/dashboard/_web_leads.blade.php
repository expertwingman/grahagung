{{-- Lead website belum ditugaskan --}}
@if($webLeadsCount > 0)
<div class="bg-white rounded-xl shadow-sm border-l-4 border-emerald-500">
    <div class="flex items-center justify-between p-4 border-b">
        <div class="flex items-center gap-2">
            <span class="text-lg">🌐</span>
            <h2 class="font-bold text-gray-700">Lead dari Website</h2>
            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $webLeadsCount }}</span>
        </div>
        <span class="text-xs text-gray-400">Belum ditugaskan</span>
    </div>
    <div class="divide-y">
        @foreach($webLeads as $lead)
        <div class="px-4 py-3 hover:bg-emerald-50">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-gray-800">{{ $lead->name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">
                        {{ $lead->wa_phone ?? $lead->phone ?? '-' }} · {{ $lead->created_at?->diffForHumans() }}
                    </div>
                    @if($lead->notes)
                    <div class="text-xs text-gray-600 mt-1">{{ Str::limit($lead->notes, 120) }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('leads.assign', $lead) }}" class="flex items-center gap-1">
                    @csrf
                    <select name="assigned_to" required class="text-xs border-gray-300 rounded py-1.5 pl-2 pr-7">
                        <option value="">Tugaskan ke…</option>
                        @foreach($salesOptions as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1.5 rounded bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700">Tugaskan</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
