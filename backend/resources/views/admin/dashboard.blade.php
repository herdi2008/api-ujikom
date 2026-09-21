@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')

    {{-- Banner Selamat Datang --}}
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

    {{-- Kartu Ringkasan (Layout Bergaya Panel Petugas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <!-- Total Alat -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalAlat }}</p>
                <p class="text-xs text-gray-400 mt-1">Total Alat</p>
            </div>
        </div>

        <!-- Peminjaman Aktif -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 022-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $peminjamanAktif }}</p>
                <p class="text-xs text-gray-400 mt-1">Peminjaman Aktif</p>
            </div>
        </div>

        <!-- Pengembalian Bulan Ini -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $pengembalianBulanIni }}</p>
                <p class="text-xs text-gray-400 mt-1">Pengembalian Bulan Ini</p>
            </div>
        </div>

        <!-- Total User -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalUser }}</p>
                <p class="text-xs text-gray-400 mt-1">Total User</p>
            </div>
        </div>

    </div>

    {{-- Log Aktivitas Terbaru --}}
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
                    @php
                        $text = strtolower($log->aktivitas);

                        if (str_contains($text, 'menambahkan')) {
                            $iconBg = 'bg-emerald-50 text-emerald-500';
                            $icon   = 'M12 4v16m8-8H4';
                        } elseif (str_contains($text, 'telat')) {
                            $iconBg = 'bg-red-50 text-red-500';
                            $icon   = 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
                        } elseif (str_contains($text, 'dikembalikan') || str_contains($text, 'selesai')) {
                            $iconBg = 'bg-blue-50 text-blue-500';
                            $icon   = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                        } elseif (str_contains($text, 'menghapus') || str_contains($text, 'dihapus') || str_contains($text, 'membatalkan')) {
                            $iconBg = 'bg-rose-50 text-rose-500';
                            $icon   = 'M6 18L18 6M6 6l12 12';
                        } elseif (str_contains($text, 'memperbarui') || str_contains($text, 'mengoreksi') || str_contains($text, 'diperbarui')) {
                            $iconBg = 'bg-amber-50 text-amber-500';
                            $icon   = 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
                        } else {
                            $iconBg = 'bg-gray-100 text-gray-500';
                            $icon   = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                        }

                        $userName = $log->user->name ?? '-';
                        $initial  = strtoupper(substr($userName, 0, 1));
                    @endphp
                    <tr class="border-t hover:bg-gray-50/60 transition">
                        <td class="px-6 py-3 text-gray-500 whitespace-nowrap">
                            <div>{{ $log->created_at->format('d M Y, H:i') }}</div>
                            <div class="text-[11px] text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gray-800 text-white text-[11px] font-semibold flex items-center justify-center flex-shrink-0">
                                    {{ $initial }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $userName }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full {{ $iconBg }} flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                    </svg>
                                </div>
                                <span>{{ $log->aktivitas }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">Belum ada aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection