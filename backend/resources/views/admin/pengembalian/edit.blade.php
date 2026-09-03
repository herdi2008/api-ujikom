@extends('layouts.admin')

@section('title', 'Koreksi Pengembalian')
@section('header-title', 'Koreksi Data Pengembalian')

@section('content')

    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-800">Peminjaman #{{ $pengembalian->peminjaman_id }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                Peminjam: <span class="font-medium text-gray-700">{{ $pengembalian->peminjaman->user->name ?? '-' }}</span>
            </p>
            <p class="text-sm text-gray-500">
                Alat:
                @foreach ($pengembalian->peminjaman->detailPinjam as $detail)
                    {{ $detail->alat->nama_alat ?? '-' }} ({{ $detail->jumlah }})@if (!$loop->last), @endif
                @endforeach
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.pengembalian.update', $pengembalian->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Alat Saat Dikembalikan</label>
                <input
                    type="text"
                    name="kondisi_kembali"
                    value="{{ old('kondisi_kembali', $pengembalian->kondisi_kembali) }}"
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
                    value="{{ old('denda', $pengembalian->denda) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                    required
                >
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.pengembalian.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-800 px-5 py-2">
                    Batal
                </a>
            </div>
        </form>

    </div>

@endsection