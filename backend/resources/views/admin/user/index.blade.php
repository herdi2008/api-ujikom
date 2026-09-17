@extends('layouts.admin')

@section('title', 'Manajemen Pengguna Sistem')
@section('header-title', 'Manajemen Pengguna Sistem')

@section('content')

    {{-- Alert sukses --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-100 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Card tabel --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="flex flex-col md:flex-row justify-between items-center gap-3 px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-700">Daftar Pengguna Sistem</h2>

            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('admin.user.index') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, role..."
                        class="border border-gray-300 rounded-l-md px-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-gray-800">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm px-4 py-2 rounded-r-md">
                        Cari
                    </button>
                    @if ($search)
                        <a href="{{ route('admin.user.index') }}" class="ml-2 self-center text-sm text-gray-500 hover:text-gray-800">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('admin.user.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md whitespace-nowrap">
                    + Tambah User
                </a>
            </div>
        </div>

        {{-- Info total, biar bisa dicocokkan dengan kartu "Total User" di dashboard --}}
        <div class="px-6 py-2.5 bg-gray-50 border-b text-xs text-gray-500">
            Menampilkan <strong>{{ $users->count() }}</strong> dari total <strong>{{ $users->total() }}</strong> pengguna
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Role / Hak Akses</th>
                    <th class="px-6 py-3">No. HP</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php
                        $colors = [
                            'admin'    => 'bg-purple-100 text-purple-700',
                            'petugas'  => 'bg-blue-100 text-blue-700',
                            'peminjam' => 'bg-green-100 text-green-700',
                        ];
                        $colorClass = $colors[strtolower($user->role)] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <tr class="border-t hover:bg-gray-50/60 transition">
                        <td class="px-6 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $user->no_hp ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.user.edit', $user->id) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white text-xs px-3 py-1.5 rounded-md mr-2">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.user.destroy', $user->id) }}" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-md">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination: sebelumnya tidak ada, jadi data di luar halaman 1 tidak pernah terlihat --}}
        <div class="px-6 py-4 border-t">
            {{ $users->links() }}
        </div>
    </div>

@endsection