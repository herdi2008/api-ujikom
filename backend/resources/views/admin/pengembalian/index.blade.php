@extends('layouts.app')

@section('title', 'Daftar Pengembalian')

@section('content')
<div class="p-6 space-y-6">

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="p-4 text-sm font-medium text-green-800 bg-green-50 rounded-xl border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Pengembalian</h1>
            <p class="text-xs text-gray-500 mt-1">
                Menampilkan <span class="font-semibold">{{ $pengembalians->total() ?? $pengembalians->count() }}</span> data (semua periode)
            </p>
        </div>

        {{-- Form Pencarian & Filter --}}
        <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari peminjam, kondisi, status, petugas..." 
                   class="w-72 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">

            <select name="bulan" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Semua Bulan</option>
                @for ($m=1; $m<=12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ request('bulan') == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>

            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition">
                Cari
            </button>

            @if(Route::has('admin.pengembalian.create'))
                <a href="{{ route('admin.pengembalian.create') }}" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                    + Tambah Pengembalian
                </a>
            @endif
        </form>
    </div>

    {{-- Tabel Pengembalian --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold">PEMINJAMAN</th>
                        <th class="px-4 py-3 font-semibold">PEMINJAM</th>
                        <th class="px-4 py-3 font-semibold">ALAT</th>
                        <th class="px-4 py-3 font-semibold">TGL KEMBALI</th>
                        <th class="px-4 py-3 font-semibold">KONDISI</th>
                        <th class="px-4 py-3 font-semibold">DENDA</th>
                        <th class="px-4 py-3 font-semibold">STATUS</th>
                        <th class="px-4 py-3 font-semibold">PETUGAS</th>
                        <th class="px-4 py-3 font-semibold text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pengembalians as $item)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-4 font-bold text-gray-900">
                                #{{ $item->peminjaman->id ?? '-' }}
                            </td>
                            <td class="px-4 py-4 font-bold text-gray-900">
                                {{ $item->peminjaman->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-600 space-y-1">
                                @foreach ($item->peminjaman->detailPinjam ?? [] as $detail)
                                    <div>
                                        {{ $detail->alat->nama_alat ?? '-' }} 
                                        <span class="text-gray-400">(x{{ $detail->jumlah }})</span>
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-4 py-4 text-xs whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($item->tgl_kembali)->format('Y-m-d') }}</div>
                                
                                {{-- PERBAIKAN: Badge dinamis sesuai bulan aktual --}}
                                @if(\Carbon\Carbon::parse($item->tgl_kembali)->isCurrentMonth())
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold text-blue-600 bg-blue-100 rounded-full">
                                        Bulan Ini
                                    </span>
                                @else
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold text-gray-600 bg-gray-100 rounded-full">
                                        {{ \Carbon\Carbon::parse($item->tgl_kembali)->format('M Y') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs font-medium">
                                {{ $item->kondisi_kembali }}
                            </td>
                            <td class="px-4 py-4 text-xs font-bold whitespace-nowrap">
                                @if($item->denda > 0)
                                    <span class="text-red-600">Rp{{ number_format($item->denda, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-full">
                                    Selesai
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $item->petugas->name ?? 'herdi firdaus' }}
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.pengembalian.edit', $item->id) }}" 
                                   class="px-3 py-1.5 text-xs font-semibold text-white bg-amber-500 rounded-md hover:bg-amber-600 transition inline-block">
                                    Edit
                                </a>
                                <form action="{{ route('admin.pengembalian.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-md hover:bg-red-700 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-400 text-sm">
                                Tidak ada data pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($pengembalians, 'links'))
            <div class="p-4 border-t border-gray-100">
                {{ $pengembalians->links() }}
            </div>
        @endif
    </div>
</div>
@endsection