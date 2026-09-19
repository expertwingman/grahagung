<x-app-layout>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('activities.index') }}" class="text-gray-400 hover:text-gray-600">← Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Aktivitas</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form method="POST" action="{{ route('activities.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: Follow up via WhatsApp">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call" {{ old('type') == 'call' ? 'selected' : '' }}>📞 Call</option>
                        <option value="meeting" {{ old('type') == 'meeting' ? 'selected' : '' }}>🤝 Meeting</option>
                        <option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>📧 Email</option>
                        <option value="whatsapp" {{ old('type') == 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                        <option value="follow_up" {{ old('type') == 'follow_up' ? 'selected' : '' }}>🔔 Follow Up</option>
                        <option value="note" {{ old('type') == 'note' ? 'selected' : '' }}>📝 Note</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="planned" {{ old('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="done" {{ old('status') == 'done' ? 'selected' : '' }}>Done</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Terkait Dengan <span class="text-red-500">*</span></label>
                    <select name="subject_type" id="subject_type" onchange="updateSubjects()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="lead">Lead</option>
                        <option value="project">Proyek</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Lead/Proyek <span class="text-red-500">*</span></label>
                    <select name="subject_id" id="subject_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih --</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" data-type="lead">{{ $lead->name }}</option>
                        @endforeach
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" data-type="project" style="display:none">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Catatan tambahan...">{{ old('description') }}</textarea>
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Simpan Aktivitas
                </button>
                <a href="{{ route('activities.index') }}" class="border border-gray-300 text-gray-600 px-6 py-2 rounded-lg hover:bg-gray-50 text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
    function updateSubjects() {
        const type = document.getElementById('subject_type').value;
        const options = document.getElementById('subject_id').querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            opt.style.display = opt.dataset.type === type ? '' : 'none';
        });
        document.getElementById('subject_id').value = '';
    }
    </script>
</x-app-layout>
