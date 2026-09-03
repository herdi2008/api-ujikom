@extends('layouts.admin')

@section('title', 'Tambah Pengembalian')
@section('header-title', 'Proses Pengembalian Baru')

@section('content')

    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.pengembalian.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peminjaman</label>
                <select
                    name="peminjaman_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                    required
                >
                    <option value="">-- Pilih peminjaman yang sedang dipinjam --</option>
                    @foreach ($peminjamans as $p)
                        <option value="{{ $p->id }}" {{ old('peminjaman_id') == $p->id ? 'selected' : '' }}>
                            #{{ $p->id }} — {{ $p->user->name ?? '-' }} (rencana kembali: {{ \Illuminate\Support\Carbon::parse($p->tgl_kembali_plan)->format('Y-m-d') }})
                        </option>
                    @endforeach
                </select>
                @if ($peminjamans->isEmpty())
                    <p class="text-xs text-gray-400 mt-1">Tidak ada peminjaman berstatus "dipinjam" saat ini.</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kembali</label>
                <input
                    type="date"
                    name="tgl_kembali"
                    value="{{ old('tgl_kembali', now()->toDateString()) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Alat Saat Dikembalikan</label>
                <input
                    type="text"
                    name="kondisi_kembali"
                    value="{{ old('kondisi_kembali') }}"
                    placeholder="mis. Baik, tidak ada kerusakan"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Denda (Rp)</label>
                <input
                    type="number"
                    name="denda"
                    min="0"
                    value="{{ old('denda', 0) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                    required
                >
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                    Proses Pengembalian
                </button>
                <a href="{{ route('admin.pengembalian.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-800 px-5 py-2">
                    Batal
                </a>
            </div>
        </form>

    </div>

@endsection