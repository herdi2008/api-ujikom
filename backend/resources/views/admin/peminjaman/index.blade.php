@extends('layouts.admin')

@section('title', 'Kelola Peminjaman')
@section('header-title', 'Manajemen Transaksi Peminjaman')

@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    
    <!-- Card Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Daftar Transaksi Peminjaman</h3>
            <p class="text-sm text-gray-400 mt-1 font-normal">{{ $peminjamans->total() }} transaksi tercatat</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.peminjaman.index') }}" method="GET" class="flex items-center">
                <div class="relative flex items-center">
                    <svg class="w-4 h-4 absolute left-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam / status..."
                        class="pl-9 pr-4 py-2.5 text-xs border border-gray-200 rounded-l-xl focus:outline-none focus:border-gray-400 w-60 md:w-72 bg-white text-gray-700">
                </div>
                <button type="submit" class="bg-gray-900 hover:bg-black text-white px-5 py-2.5 text-xs font-semibold rounded-r-xl transition">
                    Cari
                </button>
            </form>

            <a href="{{ route('admin.peminjaman.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                <span class="text-base font-bold">+</span> Tambah Peminjaman
            </a>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                <tr>
                    <th class="pb-4 px-4">PEMINJAM</th>
                    <th class="pb-4 px-4">ALAT YANG DIPINJAM</th>
                    <th class="pb-4 px-4">TGL PINJAM /<br>RENCANA KEMBALI</th>
                    <th class="pb-4 px-4">STATUS</th>
                    <th class="pb-4 px-4 text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100/70">
                @forelse($peminjamans as $item)
                    @php
                        // Normalisasi status ke huruf kecil supaya konsisten
                        // dengan cara controller menyimpan data (strtolower).
                        $statusLower = strtolower($item->status);
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <!-- Peminjam -->
                        <td class="py-5 px-4 font-bold text-gray-800 align-middle">
                            {{ $item->user->name ?? 'User Dihapus' }}
                        </td>

                        <!-- Alat Yang Dipinjam -->
                        <td class="py-5 px-4 align-middle">
                            <div class="flex flex-wrap gap-2">
                                @foreach($item->detailPinjam as $detail)
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg border border-gray-200/60 font-medium text-[11px] flex items-center gap-1">
                                        {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} 
                                        <span class="text-gray-400">×{{ $detail->jumlah }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <!-- Tanggal Pinjam / Rencana Kembali -->
                        <td class="py-5 px-4 align-middle leading-relaxed">
                            <div class="text-gray-400">Pinjam: <strong class="text-gray-700 font-bold">{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d/m/Y') }}</strong></div>
                            <div class="text-gray-400">Rencana: <strong class="text-gray-700 font-bold">{{ \Carbon\Carbon::parse($item->tgl_kembali_plan)->format('d/m/Y') }}</strong></div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-5 px-4 align-middle">
                            @if($statusLower == 'dipinjam')
                                <span class="bg-blue-100/80 text-blue-600 px-3.5 py-1.5 rounded-full text-[11px] font-semibold inline-block">Dipinjam</span>
                            @elseif($statusLower == 'selesai')
                                <span class="bg-gray-100 text-gray-600 px-3.5 py-1.5 rounded-full text-[11px] font-semibold inline-block">Selesai</span>
                            @elseif($statusLower == 'dikembalikan' || $statusLower == 'dikembali')
                                <span class="bg-emerald-100/80 text-emerald-600 px-3.5 py-1.5 rounded-full text-[11px] font-semibold inline-block">Dikembalikan</span>
                            @elseif($statusLower == 'telat')
                                <span class="bg-red-100/80 text-red-600 px-3.5 py-1.5 rounded-full text-[11px] font-semibold inline-block">Telat</span>
                            @else
                                <span class="bg-amber-100/80 text-amber-600 px-3.5 py-1.5 rounded-full text-[11px] font-semibold inline-block">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>

                        <!-- Aksi (Dropdown & Hapus) -->
                        <td class="py-5 px-4 align-middle text-center">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <form action="{{ route('admin.peminjaman.update-status', $item->id) }}" method="POST" class="w-full max-w-[110px]">
                                    @csrf
                                    @method('PATCH')
                                    <div class="relative">
                                        <select name="status" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white text-gray-700 focus:outline-none appearance-none cursor-pointer pr-6 text-center font-medium">
                                            <option value="Diajukan" {{ $statusLower == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                            <option value="Dipinjam" {{ $statusLower == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                            <option value="Selesai" {{ $statusLower == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="Dikembali" {{ ($statusLower == 'dikembali' || $statusLower == 'dikembalikan') ? 'selected' : '' }}>Dikembali</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </form>

                                <form action="{{ route('admin.peminjaman.destroy', $item->id) }}" method="POST" class="w-full max-w-[110px]">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Yakin ingin menghapus data ini?')" class="w-full bg-rose-50 text-rose-500 hover:bg-rose-100/80 py-1.5 rounded-lg text-xs font-semibold transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400">
                            Tidak ada data transaksi peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $peminjamans->links() }}
    </div>

</div>
@endsection