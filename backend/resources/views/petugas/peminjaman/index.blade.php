@extends('layouts.app')

@section('title', 'Persetujuan Peminjaman - Dashboard Petugas')
@section('header-title', 'Daftar Pengajuan Peminjaman Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Header ringkasan -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Menunggu Verifikasi Persetujuan</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $peminjamans->count() }} pengajuan menunggu persetujuan kamu</p>
        </div>
        <form action="{{ route('petugas.peminjaman.index') }}" method="GET" class="flex w-full md:w-80">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..."
                    class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2.5 text-sm font-semibold rounded-r-xl transition">
                Cari
            </button>
            @if(request('search'))
            <a href="{{ route('petugas.peminjaman.index') }}"
                class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2.5 text-sm rounded-xl flex items-center transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Daftar kartu pengajuan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($peminjamans as $item)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg flex-shrink-0">
                                {{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $item->user->name ?? 'User Dihapus' }}</p>
                                <p class="text-xs text-gray-500">Mengajukan peminjaman</p>
                            </div>
                        </div>
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0">
                            Diajukan
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal Pinjam</p>
                            <p class="font-semibold text-gray-800">{{ $item->tgl_pinjam->format('d-m-Y') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 mb-0.5">Rencana Kembali</p>
                            <p class="font-semibold text-gray-800">{{ $item->tgl_kembali_plan->format('d-m-Y') }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-2 font-medium uppercase tracking-wide">Detail Alat</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($item->detailPinjam as $detail)
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium px-2.5 py-1.5 rounded-lg">
                                    {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                    <span class="text-blue-400">×{{ $detail->jumlah }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Setujui peminjaman alat ini?')"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-semibold transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui Peminjaman
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 bg-white rounded-2xl border border-dashed border-gray-300 py-16 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 font-medium">Tidak ada pengajuan peminjaman baru</p>
                <p class="text-gray-400 text-sm mt-1">Semua pengajuan sudah diproses</p>
            </div>
        @endforelse
    </div>
@endsection