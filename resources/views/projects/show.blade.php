<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600">← Kembali</a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.edit', $project) }}"
               class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 text-sm font-medium">Edit</a>
            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm font-medium">Hapus</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Proyek</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Nama Proyek</p>
                    <p class="font-medium text-gray-800">{{ $project->name }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Lead Terkait</p>
                    <p class="font-medium text-gray-800">{{ $project->lead->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Tanggal Mulai</p>
                    <p class="font-medium text-gray-800">{{ $project->start_date?->format('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Deadline</p>
                    <p class="font-medium text-gray-800">{{ $project->due_date?->format('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Nilai Proyek</p>
                    <p class="font-medium text-gray-800">{{ $project->value ? 'Rp '.number_format($project->value, 0, ',', '.') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Ditambahkan</p>
                    <p class="font-medium text-gray-800">{{ $project->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t">
                <p class="text-gray-400 text-sm mb-2">Progress</p>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full transition-all" style="width: {{ $project->progress }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">{{ number_format($project->progress, 0) }}%</span>
                </div>
            </div>

            @if($project->description)
            <div class="mt-4 pt-4 border-t">
                <p class="text-gray-400 text-sm mb-1">Deskripsi</p>
                <p class="text-sm text-gray-700">{{ $project->description }}</p>
            </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-2">Status</p>
                @php
                    $statusColors = [
                        'planning'    => 'bg-blue-100 text-blue-700',
                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                        'on_hold'     => 'bg-gray-100 text-gray-700',
                        'completed'   => 'bg-green-100 text-green-700',
                        'cancelled'   => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-2">Prioritas</p>
                @php
                    $priorityColors = [
                        'low'    => 'bg-gray-100 text-gray-600',
                        'medium' => 'bg-yellow-100 text-yellow-700',
                        'high'   => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $priorityColors[$project->priority] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($project->priority) }}
                </span>
            </div>

            @if($project->completed_date)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-1">Selesai Pada</p>
                <p class="text-sm font-medium text-green-600">{{ $project->completed_date->format('d M Y') }}</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
