<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Aktivitas</h1>
        <a href="{{ route('activities.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            + Tambah Aktivitas
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aktivitas</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Tipe</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Terkait</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Jadwal</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($activities as $activity)
                @php
                    $typeIcons = [
                        'call'          => '📞',
                        'meeting'       => '🤝',
                        'email'         => '📧',
                        'whatsapp'      => '💬',
                        'follow_up'     => '🔔',
                        'note'          => '📝',
                        'status_change' => '🔄',
                    ];
                    $statusColors = [
                        'planned'   => 'bg-blue-100 text-blue-700',
                        'done'      => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $activity->title }}</p>
                        @if($activity->description)
                            <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($activity->description, 60) }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm">{{ $typeIcons[$activity->type] ?? '📌' }}</span>
                        <span class="text-gray-600 ml-1">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($activity->subject)
                            {{ $activity->subject->name }}
                            <span class="text-xs text-gray-400">({{ class_basename($activity->subject_type) }})</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$activity->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($activity->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $activity->scheduled_at ? $activity->scheduled_at->format('d M Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            @if($activity->status === 'planned')
                                <form method="POST" action="{{ route('activities.done', $activity) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-green-50 text-green-600 border border-green-200 px-3 py-1 rounded text-xs font-medium hover:bg-green-100">
                                        Selesai
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('activities.destroy', $activity) }}" onsubmit="return confirm('Hapus aktivitas ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded text-xs font-medium hover:bg-red-100">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        Belum ada aktivitas. <a href="{{ route('activities.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($activities->hasPages())
            <div class="px-4 py-3 border-t">{{ $activities->links() }}</div>
        @endif
    </div>
</x-app-layout>
