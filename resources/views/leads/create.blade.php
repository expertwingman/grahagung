<x-app-layout>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('leads.index') }}" class="text-gray-400 hover:text-gray-600">← Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Lead Baru</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form method="POST" action="{{ route('leads.store') }}">
            @csrf

            <div class="grid grid-cols-2 gap-4">

                {{-- Nama --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Nama lengkap">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp</label>
                    <div class="flex gap-2">
                        <select name="phone_code" class="border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-32">
                            <option value="62" {{ old('phone_code', '62') == '62' ? 'selected' : '' }}>🇮🇩 +62</option>
                            <option value="60" {{ old('phone_code') == '60' ? 'selected' : '' }}>🇲🇾 +60</option>
                            <option value="65" {{ old('phone_code') == '65' ? 'selected' : '' }}>🇸🇬 +65</option>
                            <option value="63" {{ old('phone_code') == '63' ? 'selected' : '' }}>🇵🇭 +63</option>
                            <option value="66" {{ old('phone_code') == '66' ? 'selected' : '' }}>🇹🇭 +66</option>
                            <option value="84" {{ old('phone_code') == '84' ? 'selected' : '' }}>🇻🇳 +84</option>
                            <option value="95" {{ old('phone_code') == '95' ? 'selected' : '' }}>🇲🇲 +95</option>
                            <option value="61" {{ old('phone_code') == '61' ? 'selected' : '' }}>🇦🇺 +61</option>
                            <option value="1"  {{ old('phone_code') == '1'  ? 'selected' : '' }}>🇺🇸 +1</option>
                            <option value="44" {{ old('phone_code') == '44' ? 'selected' : '' }}>🇬🇧 +44</option>
                            <option value="91" {{ old('phone_code') == '91' ? 'selected' : '' }}>🇮🇳 +91</option>
                            <option value="86" {{ old('phone_code') == '86' ? 'selected' : '' }}>🇨🇳 +86</option>
                            <option value="81" {{ old('phone_code') == '81' ? 'selected' : '' }}>🇯🇵 +81</option>
                            <option value="82" {{ old('phone_code') == '82' ? 'selected' : '' }}>🇰🇷 +82</option>
                        </select>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="8123456789">
                    </div>
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="email@example.com">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Perusahaan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Perusahaan</label>
                    <input type="text" name="company" value="{{ old('company') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Nama perusahaan">
                </div>

                {{-- Sumber --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sumber Lead</label>
                    <select name="source" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Sumber --</option>
                        <option value="instagram" {{ old('source') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="facebook" {{ old('source') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="google" {{ old('source') == 'google' ? 'selected' : '' }}>Google</option>
                        <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>Referral</option>
                        <option value="whatsapp" {{ old('source') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>Website</option>
                        <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(\App\Models\Lead::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ old('status', 'no_respon') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nilai Deal --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Deal (Rp)</label>
                    <input type="number" name="value" value="{{ old('value') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0">
                </div>

                {{-- Kota --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Jakarta">
                </div>

                {{-- Alamat --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Alamat lengkap">
                </div>

                {{-- Ketertarikan Produk --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ketertarikan Produk</label>
                    <select name="product_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Produk --</option>
                        @foreach(\App\Models\Product::where('is_active', true)->orderBy('name')->get() as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Catatan Ketertarikan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Ketertarikan</label>
                    <input type="text" name="interest_notes" value="{{ old('interest_notes') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Detail ketertarikan lead...">
                </div>

                {{-- Catatan --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                </div>

            </div>
            {{-- Section Properti --}}
<div class="border-t pt-4 mt-2">
    <p class="text-sm font-semibold text-gray-600 mb-3">📋 Detail Properti</p>
    <div class="grid grid-cols-2 gap-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Minat Tipe Unit</label>
            <input type="text" name="interest_type" value="{{ old('interest_type') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Ex: Type 36, Type 45, Kavling">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Range Budget</label>
            <select name="budget_range" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Range --</option>
                <option value="< 300jt" {{ old('budget_range') == '< 300jt' ? 'selected' : '' }}>&lt; 300jt</option>
                <option value="300-500jt" {{ old('budget_range') == '300-500jt' ? 'selected' : '' }}>300 - 500jt</option>
                <option value="500jt-1M" {{ old('budget_range') == '500jt-1M' ? 'selected' : '' }}>500jt - 1M</option>
                <option value="1M-2M" {{ old('budget_range') == '1M-2M' ? 'selected' : '' }}>1M - 2M</option>
                <option value="> 2M" {{ old('budget_range') == '> 2M' ? 'selected' : '' }}>&gt; 2M</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Minat</label>
            <input type="text" name="location_interest" value="{{ old('location_interest') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Ex: Blok A, Cluster B">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Follow Up Terakhir</label>
            <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Survey</label>
            <input type="text" name="survey_plan" value="{{ old('survey_plan') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Tanggal/rencana survey">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Survey</label>
            <input type="text" name="survey_result" value="{{ old('survey_result') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Hasil setelah survey">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <input type="checkbox" name="utj_status" id="utj_status" value="1"
                {{ old('utj_status') ? 'checked' : '' }} class="rounded w-4 h-4">
            <label for="utj_status" class="text-sm font-medium text-gray-700">UTJ (Uang Tanda Jadi)</label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal UTJ</label>
            <input type="date" name="utj_date" value="{{ old('utj_date') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pending / Batal</label>
            <input type="text" name="cancel_reason" value="{{ old('cancel_reason') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Jika lead pending atau batal, isi alasannya">
        </div>

    </div>
</div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Simpan Lead
                </button>
                <a href="{{ route('leads.index') }}" class="border border-gray-300 text-gray-600 px-6 py-2 rounded-lg hover:bg-gray-50 text-sm">
                    Batal
                </a>
            </div>

        </form>
    </div>
</x-app-layout>
