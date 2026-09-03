<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex">
            <div class="p-5 text-xl font-bold tracking-wider border-b border-gray-800">
                @if(auth()->user()->role === 'admin')
                    PANEL ADMIN
                @elseif(auth()->user()->role === 'petugas')
                    PANEL PETUGAS
                @else
                    PANEL PEMINJAM
                @endif
            </div>
            <nav class="flex-1 p-4 space-y-2">

                {{-- MENU KHUSUS ADMIN --}}
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Dashboard</a>

                    <a href="{{ route('admin.alat.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.alat*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Alat</a>

                    <a href="{{ route('admin.kategori.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.kategori*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Kategori</a>

                    <a href="{{ route('admin.peminjaman.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Peminjaman</a>

                    <a href="{{ route('admin.pengembalian.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.pengembalian*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Pengembalian</a>

                    <a href="{{ route('admin.log.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.log*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Log Aktivitas</a>

                    <a href="{{ route('admin.user.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.user*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola User</a>

                {{-- MENU KHUSUS PETUGAS --}}
                @elseif(auth()->user()->role === 'petugas')
                    <a href="{{ route('petugas.peminjaman.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Persetujuan Peminjaman</a>

                    <a href="{{ route('petugas.pengembalian.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.pengembalian*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Pemantauan Pengembalian</a>

                    <a href="{{ route('petugas.laporan.index') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.laporan*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Cetak Laporan</a>

                {{-- MENU KHUSUS PEMINJAM --}}
                @elseif(auth()->user()->role === 'peminjam')
                    <a href="{{ route('peminjam.katalog') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.katalog') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Katalog Alat</a>

                    <a href="{{ route('peminjam.riwayat') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.riwayat') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Riwayat & Pengembalian</a>
                @endif

            </nav>
            <div class="p-4 border-t border-gray-800 space-y-3">
                <div class="text-sm text-gray-400">
                    Logged in as: <span class="text-white font-semibold">{{ auth()->user()->name }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition border border-red-500/20 hover:border-red-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>
        <div class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-white shadow-sm h-16 flex items-center px-6 z-10">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Dashboard')
                </div>
            </header>
            <main class="flex-1 p-6">
                @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
                @endif
                @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>