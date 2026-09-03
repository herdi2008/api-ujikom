@extends('layouts.admin')

@section('title', 'Kelola Pengembalian')
@section('header-title', 'Kelola Pengembalian')

@section('content')

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        <div class="flex items-center justify-between p-5 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800">Daftar Pengembalian</h2>

            <div class="flex items-center gap-3">
                <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari peminjam, kondisi, status, petugas..."
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-gray-800"
                    >
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition">
                        Cari
                    </button>
                    @if ($search)
                        <a href="{{ route('admin.pengembalian.index') }}" class="text-sm text-gray-500 hover:text-gray-800 self-center">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('admin.pengembalian.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition whitespace-nowrap">
                    + Tambah Pengembalian
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Peminjaman</th>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Alat</th>
                        <th class="px-5 py-3">Tgl Kembali</th>
                        <th class="px-5 py-3">Kondisi</th>
                        <th class="px-5 py-3">Denda</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Petugas</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pengembalians as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-800">#{{ $item->peminjaman_id }}</td>
                            <td class="px-5 py-3">{{ $item->peminjaman->user->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-600">
                                @foreach ($item->peminjaman->detailPinjam as $detail)
                                    {{ $detail->alat->nama_alat ?? '-' }} ({{ $detail->jumlah }})@if (!$loop->last), @endif
                                @endforeach
                            </td>
                            <td class="px-5 py-3">{{ \Illuminate\Support\Carbon::parse($item->tgl_kembali)->format('Y-m-d') }}</td>
                            <td class="px-5 py-3">{{ $item->kondisi_kembali }}</td>
                            <td class="px-5 py-3">
                                @if ($item->denda > 0)
                                    <span class="text-red-600 font-medium">Rp{{ number_format($item->denda, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $status = $item->peminjaman->status ?? '-';
                                    $badge = match ($status) {
                                        'selesai' => 'bg-green-100 text-green-700',
                                        'telat' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">{{ $item->petugas->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.pengembalian.edit', $item->id) }}"
                                       class="inline-block bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.pengembalian.destroy', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Batalkan data pengembalian ini? Status peminjaman akan dikembalikan ke \'dipinjam\' dan stok alat ditarik kembali.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-4 py-2 rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-gray-400">
                                Belum ada data pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-200">
            {{ $pengembalians->links() }}
        </div>

    </div>

@endsection