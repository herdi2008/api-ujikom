@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian - Dashboard Petugas')
@section('header-title', 'Pemantauan & Proses Pengembalian Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Ringkasan cepat --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $peminjamans->count() }}</p>
                <p class="text-sm text-gray-500">Peminjaman Aktif</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $peminjamans->where('status', 'telat')->count() }}</p>
                <p class="text-sm text-gray-500">Terlambat</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $peminjamans->sum(fn($p) => $p->detailPinjam->sum('jumlah')) }}</p>
                <p class="text-sm text-gray-500">Total Unit Dipinjam</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Daftar Peminjaman Aktif</h3>
                <p class="text-sm text-gray-500">Alat yang belum dikembalikan oleh peminjam</p>
            </div>
            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('petugas.pengembalian.index') }}"
                    class="ml-2 bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 text-sm rounded-lg flex items-center transition">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <div class="p-5">
            @forelse($peminjamans as $item)
                @php
                    $isTelat = $item->status == 'telat';
                @endphp
                <div class="mb-4 last:mb-0 rounded-2xl border {{ $isTelat ? 'border-red-200' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col lg:flex-row">

                        {{-- Kolom kiri: info peminjam --}}
                        <div class="lg:w-64 flex-shrink-0 p-5 {{ $isTelat ? 'bg-red-50/60' : 'bg-gray-50' }} border-b lg:border-b-0 lg:border-r border-gray-200 flex lg:flex-col gap-4 lg:gap-3 items-center lg:items-start">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0
                                {{ $isTelat ? 'bg-red-500' : 'bg-gradient-to-br from-emerald-500 to-teal-600' }}">
                                {{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900 leading-tight">{{ $item->user->name ?? 'User Dihapus' }}</p>
                                <span class="inline-flex items-center gap-1 mt-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $isTelat ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isTelat ? 'bg-red-500 animate-pulse' : 'bg-blue-500' }}"></span>
                                    {{ ucfirst($item->status) }}
                                </span>
                                <div class="mt-3 space-y-1 text-xs text-gray-500">
                                    <p class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Pinjam: <span class="font-medium text-gray-700">{{ $item->tgl_pinjam->format('d-m-Y') }}</span>
                                    </p>
                                    <p class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Kembali: <span class="font-medium text-gray-700">{{ $item->tgl_kembali_plan->format('d-m-Y') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom tengah: detail alat --}}
                        <div class="flex-1 p-5">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Detail Alat Dipinjam</p>
                            <div class="grid sm:grid-cols-2 gap-2">
                                @foreach($item->detailPinjam as $detail)
                                    <div class="flex items-center justify-between gap-3 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-800 truncate">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-500 bg-white border border-gray-200 rounded-full px-2 py-0.5 flex-shrink-0">
                                            x{{ $detail->jumlah }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Kolom kanan: aksi pengembalian --}}
                        <div class="lg:w-72 flex-shrink-0 p-5 bg-gray-50 border-t lg:border-t-0 lg:border-l border-gray-200">
                            <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kondisi Kembali</label>
                                    <select name="kondisi_kembali" required
                                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white
                                            focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                        <option value="Baik">✅ Baik</option>
                                        <option value="Rusak Ringan">⚠️ Rusak Ringan</option>
                                        <option value="Rusak Berat">🛑 Rusak Berat</option>
                                    </select>
                                </div>
                                <button type="submit" onclick="return confirm('Proses pengembalian alat ini?')"
                                    class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700
                                        text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Terima Pengembalian
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @empty
                <div class="py-16 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">Tidak ada peminjaman yang sedang aktif saat ini</p>
                    <p class="text-sm text-gray-400 mt-1">Semua alat sudah kembali, atau coba kata kunci pencarian lain.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection