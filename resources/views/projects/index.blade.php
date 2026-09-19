<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Proyek</h1>
        <a href="{{ route('projects.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            + Tambah Proyek
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
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama Proyek</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Lead</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Prioritas</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Progress</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Due Date</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($projects as $project)
                @php
                    $statusColors = [
                        'planning'    => 'bg-blue-100 text-blue-700',
                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                        'on_hold'     => 'bg-gray-100 text-gray-700',
                        'completed'   => 'bg-green-100 text-green-700',
                        'cancelled'   => 'bg-red-100 text-red-700',
                    ];
                    $priorityColors = [
                        'low'    => 'bg-gray-100 text-gray-600',
                        'medium' => 'bg-yellow-100 text-yellow-700',
                        'high'   => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $project->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $project->lead->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $priorityColors[$project->priority] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($project->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $project->progress }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ number_format($project->progress, 0) }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <a href="{{ route('projects.show', $project) }}"
                               class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded text-xs font-medium hover:bg-blue-100">Lihat</a>
                            <a href="{{ route('projects.edit', $project) }}"
                               class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-3 py-1 rounded text-xs font-medium hover:bg-yellow-100">Edit</a>
                            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded text-xs font-medium hover:bg-red-100">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        Belum ada proyek. <a href="{{ route('projects.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($projects->hasPages())
            <div class="px-4 py-3 border-t">{{ $projects->links() }}</div>
        @endif
    </div>
</x-app-layout>
