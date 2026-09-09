@extends('layouts.app')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat & Pengembalian Alat')

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

    <div class="space-y-4">
        @forelse($peminjamans as $item)
            @php
                $badge = match($item->status) {
                    'diajukan' => ['bg-yellow-100 text-yellow-700', 'Menunggu Persetujuan'],
                    'dipinjam' => ['bg-blue-100 text-blue-700', 'Sedang Dipinjam'],
                    'telat' => ['bg-red-100 text-red-700', 'Telat Dikembalikan'],
                    'dikembalikan' => ['bg-purple-100 text-purple-700', 'Menunggu Verifikasi'],
                    'selesai' => ['bg-emerald-100 text-emerald-700', 'Selesai'],
                    default => ['bg-gray-100 text-gray-700', ucfirst($item->status)],
                };
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Peminjaman #{{ $item->id }}</p>
                            <p class="font-bold text-gray-900">{{ $item->tgl_pinjam->format('d-m-Y') }} — {{ $item->tgl_kembali_plan->format('d-m-Y') }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $badge[0] }} flex-shrink-0">
                            {{ $badge[1] }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-2 font-medium uppercase tracking-wide">Alat Dipinjam</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($item->detailPinjam as $detail)
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium px-2.5 py-1.5 rounded-lg">
                                    {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                    <span class="text-blue-400">×{{ $detail->jumlah }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    @if($item->pengembalian)
                        <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm flex justify-between">
                            <span class="text-gray-600">Denda</span>
                            <span class="font-semibold text-gray-900">Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if(in_array($item->status, ['dipinjam', 'telat']))
                        <form action="{{ route('peminjam.riwayat.ajukanPengembalian', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Ajukan pengembalian alat ini? Pastikan alat sudah kamu serahkan ke petugas.')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold transition shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Ajukan Pengembalian
                            </button>
                        </form>
                    @elseif($item->status === 'dikembalikan')
                        <p class="text-center text-xs text-purple-600 font-medium bg-purple-50 py-2 rounded-lg">
                            Menunggu verifikasi petugas
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 py-16 text-center">
                <p class="text-gray-500 font-medium">Kamu belum pernah meminjam alat</p>
                <a href="{{ route('peminjam.katalog') }}" class="text-emerald-600 text-sm font-semibold hover:underline mt-2 inline-block">Lihat Katalog Alat →</a>
            </div>
        @endforelse
    </div>
@endsection