@extends('layouts.app')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog & Pengajuan Peminjaman')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST" id="form-pinjam">
        @csrf

        <!-- Filter -->
        <div class="flex flex-col md:flex-row gap-3 mb-6">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="search-input" value="{{ $search }}" placeholder="Cari nama alat..."
                    class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <select id="kategori-select" class="px-3 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k->id }}" {{ $kategori_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <!-- Grid katalog -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-28">
            @forelse($alats as $alat)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                    <div class="h-36 bg-gray-100 flex items-center justify-center">
                        @if($alat->gambar)
                            <img src="{{ asset($alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="h-full w-full object-cover">
                        @else
                            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h4 class="font-bold text-gray-900 text-sm">{{ $alat->nama_alat }}</h4>
                            <span class="text-[11px] bg-emerald-100 text-emerald-700 font-semibold px-2 py-0.5 rounded-full flex-shrink-0">
                                Stok {{ $alat->stok }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">{{ $alat->kategori->nama_kategori ?? '-' }}</p>

                        <label class="flex items-center gap-2 mb-2 cursor-pointer">
                            <input type="checkbox" class="chk-alat w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                data-id="{{ $alat->id }}" data-nama="{{ $alat->nama_alat }}" data-stok="{{ $alat->stok }}">
                            <span class="text-xs font-medium text-gray-700">Pilih alat ini</span>
                        </label>
                        <input type="number" class="input-jumlah w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 disabled:bg-gray-100 disabled:text-gray-400"
                            data-id="{{ $alat->id }}" min="1" max="{{ $alat->stok }}" value="1" disabled placeholder="Jumlah">
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-300 py-16 text-center">
                    <p class="text-gray-500 font-medium">Tidak ada alat yang tersedia saat ini</p>
                </div>
            @endforelse
        </div>

        <!-- Panel pengajuan (fixed bottom) -->
        <div class="fixed bottom-0 left-0 md:left-64 right-0 bg-white border-t border-gray-200 shadow-lg p-4">
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" required min="{{ date('Y-m-d') }}"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Rencana Kembali</label>
                    <input type="date" name="tgl_kembali_plan" required min="{{ date('Y-m-d') }}"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div id="selected-count" class="text-xs text-gray-500 whitespace-nowrap pb-2 md:pb-0">0 alat dipilih</div>
                <button type="submit" id="btn-ajukan" disabled
                    class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm whitespace-nowrap">
                    Ajukan Peminjaman
                </button>
            </div>
        </div>
    </form>

    <script>
        // Filter search & kategori (reload dengan query string)
        function applyFilter() {
            const search = document.getElementById('search-input').value;
            const kategori_id = document.getElementById('kategori-select').value;
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (kategori_id) params.set('kategori_id', kategori_id);
            window.location.href = "{{ route('peminjam.katalog') }}?" + params.toString();
        }
        document.getElementById('kategori-select').addEventListener('change', applyFilter);
        document.getElementById('search-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); applyFilter(); }
        });

        // Toggle checkbox -> aktifkan input jumlah & buat hidden input untuk submit
        const form = document.getElementById('form-pinjam');
        const checkboxes = document.querySelectorAll('.chk-alat');
        const btnAjukan = document.getElementById('btn-ajukan');
        const selectedCount = document.getElementById('selected-count');

        function updateState() {
            let count = 0;
            // Hapus hidden input lama
            form.querySelectorAll('input[type=hidden].dynamic-field').forEach(el => el.remove());

            checkboxes.forEach(chk => {
                const id = chk.dataset.id;
                const jumlahInput = document.querySelector(`.input-jumlah[data-id="${id}"]`);
                jumlahInput.disabled = !chk.checked;

                if (chk.checked) {
                    count++;
                    const hiddenId = document.createElement('input');
                    hiddenId.type = 'hidden';
                    hiddenId.name = 'alat_id[]';
                    hiddenId.value = id;
                    hiddenId.classList.add('dynamic-field');
                    form.appendChild(hiddenId);

                    const hiddenJumlah = document.createElement('input');
                    hiddenJumlah.type = 'hidden';
                    hiddenJumlah.name = 'jumlah[]';
                    hiddenJumlah.value = jumlahInput.value;
                    hiddenJumlah.classList.add('dynamic-field');
                    form.appendChild(hiddenJumlah);
                }
            });

            selectedCount.textContent = count + ' alat dipilih';
            btnAjukan.disabled = count === 0;
        }

        checkboxes.forEach(chk => chk.addEventListener('change', updateState));
        document.querySelectorAll('.input-jumlah').forEach(inp => inp.addEventListener('input', updateState));
    </script>
@endsection