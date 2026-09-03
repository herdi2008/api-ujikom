@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')

    {{-- Banner selamat datang --}}
    @php
        $roleColors = [
            'admin'    => 'bg-purple-100 text-purple-700',
            'petugas'  => 'bg-blue-100 text-blue-700',
            'peminjam' => 'bg-green-100 text-green-700',
        ];
        $roleColorClass = $roleColors[strtolower(auth()->user()->role)] ?? 'bg-gray-100 text-gray-700';
    @endphp
    <div class="bg-green-50 border border-green-100 rounded-lg px-6 py-4 mb-6 text-sm">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Anda login sebagai hak akses
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $roleColorClass }}">
            {{ strtoupper(auth()->user()->role) }}
        </span>.
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs uppercase text-gray-500 tracking-wide">Total Alat</p>
            <p class="text-2xl font-semibold mt-1">{{ $totalAlat }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs uppercase text-gray-500 tracking-wide">Peminjaman Aktif</p>
            <p class="text-2xl font-semibold mt-1 text-blue-600">{{ $peminjamanAktif }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs uppercase text-gray-500 tracking-wide">Pengembalian Bulan Ini</p>
            <p class="text-2xl font-semibold mt-1 text-green-600">{{ $pengembalianBulanIni }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs uppercase text-gray-500 tracking-wide">Total User</p>
            <p class="text-2xl font-semibold mt-1">{{ $totalUser }}</p>
        </div>
    </div>

    {{-- Log aktivitas --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-700">Log Aktivitas Terbaru</h2>
            <span class="text-xs text-gray-400">Menampilkan aktivitas sistem secara nyata</span>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                    <th class="px-6 py-3">Waktu</th>
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t">
                        <td class="px-6 py-3 text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-3 font-medium text-gray-800">{{ $log->user->name ?? '-' }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $log->aktivitas }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-400">Belum ada aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection