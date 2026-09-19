<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('leads.index') }}" class="text-gray-400 hover:text-gray-600">← Kembali</a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $lead->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('leads.edit', $lead) }}"
               class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 text-sm font-medium">Edit</a>
            <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('Hapus lead ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm font-medium">Hapus</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Informasi Lead</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Nama</p>
                    <p class="font-medium text-gray-800">{{ $lead->name }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Perusahaan</p>
                    <p class="font-medium text-gray-800">{{ $lead->company ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">No. HP</p>
                    <p class="font-medium text-gray-800">{{ $lead->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Email</p>
                    <p class="font-medium text-gray-800">{{ $lead->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Kota</p>
                    <p class="font-medium text-gray-800">{{ $lead->city ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Sumber</p>
                    <p class="font-medium text-gray-800">{{ ucfirst($lead->source ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Alamat</p>
                    <p class="font-medium text-gray-800">{{ $lead->address ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">WA Phone</p>
                    <p class="font-medium text-gray-800">{{ $lead->wa_phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Ketertarikan Produk</p>
                    <p class="font-medium text-gray-800">
                        @if($lead->product)
                            <span class="bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded text-xs">
                                {{ $lead->product->name }}
                            </span>
                        @else
                            -
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-gray-400">Catatan Ketertarikan</p>
                    <p class="font-medium text-gray-800">{{ $lead->interest_notes ?? '-' }}</p>
                </div>
            </div>
            </div>

{{-- Section Properti --}}
<div class="mt-4 pt-4 border-t">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">📋 Detail Properti</p>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-gray-400">Minat Tipe Unit</p>
            <p class="font-medium text-gray-800">{{ $lead->interest_type ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-400">Range Budget</p>
            <p class="font-medium text-gray-800">{{ $lead->budget_range ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-400">Lokasi Minat</p>
            <p class="font-medium text-gray-800">{{ $lead->location_interest ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-400">Follow Up Terakhir</p>
            <p class="font-medium text-gray-800">{{ $lead->follow_up_date?->format('d M Y') ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-400">Rencana Survey</p>
            <p class="font-medium text-gray-800">{{ $lead->survey_plan ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-400">Hasil Survey</p>
            <p class="font-medium text-gray-800">{{ $lead->survey_result ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-400">UTJ</p>
            <p class="font-medium">
                @if($lead->utj_status)
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">✅ Sudah UTJ</span>
                    @if($lead->utj_date)
                        <span class="text-gray-500 text-xs ml-1">{{ $lead->utj_date->format('d M Y') }}</span>
                    @endif
                @else
                    <span class="text-gray-400">Belum UTJ</span>
                @endif
            </p>
        </div>
        <div>
            <p class="text-gray-400">Alasan Pending/Batal</p>
            <p class="font-medium text-gray-800">{{ $lead->cancel_reason ?? '-' }}</p>
        </div>
    </div>
</div>

            @if($lead->notes)
            <div class="mt-4 pt-4 border-t">
                <p class="text-gray-400 text-sm mb-1">Catatan</p>
                <p class="text-sm text-gray-700">{{ $lead->notes }}</p>
            </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-2">Status</p>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $lead->statusColor() }}">
                    {{ $lead->statusLabel() }}
                </span>
                </span>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-1">Nilai Deal</p>
                <p class="text-xl font-bold text-gray-800">
                    {{ $lead->value ? 'Rp ' . number_format($lead->value, 0, ',', '.') : '-' }}
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-1">Ditambahkan</p>
                <p class="text-sm font-medium text-gray-800">{{ $lead->created_at->format('d M Y') }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 mb-2">Hubungi via WhatsApp</p>
                @if($lead->wa_phone)
                    <a href="https://wa.me/{{ $lead->wa_phone }}" target="_blank"
                       class="block w-full bg-green-500 text-white text-center px-4 py-2 rounded-lg hover:bg-green-600 text-sm font-medium">
                        💬 Buka WhatsApp
                    </a>
                @else
                    <p class="text-xs text-gray-400">Nomor WA tidak tersedia</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline Aktivitas --}}
<div class="mt-6 grid grid-cols-3 gap-6">

    {{-- Form Tambah Aktivitas --}}
    <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-4">📝 Tambah Catatan</h3>
        <form method="POST" action="{{ route('activities.store') }}">
            @csrf
            <input type="hidden" name="subject_type" value="lead">
            <input type="hidden" name="subject_id" value="{{ $lead->id }}">
            <input type="hidden" name="status" value="done">
            <input type="hidden" name="title" id="activity-title" value="Catatan">

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Jenis</label>
                    <select name="type" onchange="updateActivityTitle(this.value)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call">📞 Telepon</option>
                        <option value="whatsapp">💬 WhatsApp</option>
                        <option value="meeting">🤝 Meeting</option>
                        <option value="follow_up">🔔 Follow Up</option>
                        <option value="note">📝 Catatan</option>
                        <option value="email">📧 Email</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="description" rows="3" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Isi catatan interaksi..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal & Waktu</label>
                    <input type="datetime-local" name="scheduled_at"
                        value="{{ now()->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                    + Simpan Catatan
                </button>
            </div>
        </form>
        <script>
        function updateActivityTitle(type) {
            const labels = {
                'call': 'Telepon', 'whatsapp': 'WhatsApp', 'meeting': 'Meeting',
                'follow_up': 'Follow Up', 'note': 'Catatan', 'email': 'Email'
            };
            document.getElementById('activity-title').value = labels[type] || 'Aktivitas';
        }
        </script>
    </div>

    {{-- Timeline --}}
    <div class="col-span-2 bg-white rounded-lg shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-4">🕐 Riwayat Aktivitas</h3>

        @php
            $activities = $lead->activities()->with('createdBy')->get();
            $typeIcons = [
                'call'          => '📞',
                'whatsapp'      => '💬',
                'meeting'       => '🤝',
                'follow_up'     => '🔔',
                'note'          => '📝',
                'email'         => '📧',
                'status_change' => '🔄',
            ];
            $typeLabels = [
                'call'          => 'Telepon',
                'whatsapp'      => 'WhatsApp',
                'meeting'       => 'Meeting',
                'follow_up'     => 'Follow Up',
                'note'          => 'Catatan',
                'email'         => 'Email',
                'status_change' => 'Perubahan Status',
            ];
        @endphp

        @if($activities->isEmpty())
            <div class="text-center text-gray-400 py-8">
                <p class="text-3xl mb-2">📭</p>
                <p class="text-sm">Belum ada aktivitas. Tambahkan catatan pertama!</p>
            </div>
        @else
            <div class="relative">
                {{-- Garis vertikal timeline --}}
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                <div class="space-y-4">
                    @foreach($activities as $activity)
                    <div class="relative flex gap-4">
                        {{-- Dot --}}
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 z-10 text-sm">
                            {{ $typeIcons[$activity->type] ?? '📌' }}
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 bg-gray-50 rounded-lg p-3 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-xs font-semibold text-blue-600">
                                    {{ $typeLabels[$activity->type] ?? $activity->type }}
                                </span>
                                <span class="text-xs text-gray-400 flex-shrink-0">
                                    {{ $activity->created_at->locale('id')->diffForHumans() }}
                                </span>
                            </div>

                            @if($activity->description)
                                <p class="text-sm text-gray-700">{{ $activity->description }}</p>
                            @endif

                            @if($activity->scheduled_at)
                                <p class="text-xs text-gray-400 mt-1">
                                    📅 {{ $activity->scheduled_at->format('d M Y, H:i') }}
                                </p>
                            @endif

                            <p class="text-xs text-gray-400 mt-1">
                                oleh {{ $activity->createdBy->name ?? 'Sistem' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
