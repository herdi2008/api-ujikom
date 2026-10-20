<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: float 8s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.05); }
        }

        .animate-fade-up {
            animation: fadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-glow:focus-within {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4 overflow-hidden relative">

    <!-- Background Decorative Blobs -->
    <div class="blob w-96 h-96 bg-indigo-600 top-[-10%] left-[-5%]"></div>
    <div class="blob w-96 h-96 bg-cyan-500 bottom-[-10%] right-[-5%]" style="animation-delay: 2s;"></div>
    <div class="blob w-72 h-72 bg-purple-600 top-[40%] left-[45%]" style="animation-delay: 4s;"></div>

    <div class="relative w-full max-w-4xl bg-white/[0.03] backdrop-blur-2xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden animate-fade-up flex flex-col md:flex-row">

        <!-- LEFT SIDE: Branding / Ilustrasi -->
        <div class="hidden md:flex md:w-1/2 flex-col justify-between p-10 bg-gradient-to-br from-indigo-600/20 to-cyan-500/10 border-r border-white/10 relative">
            <div>
                <div class="flex items-center gap-2 mb-16">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-cyan-400 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m-2-9h14M5 8h14M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4" />
                        </svg>
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">AlatKu</span>
                </div>

                <h1 class="text-3xl font-extrabold text-white leading-tight mb-4">
                    Kelola peminjaman alat lebih rapi & efisien.
                </h1>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Satu sistem untuk admin, petugas lab, dan peminjam — semua data tercatat otomatis dan real-time.
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-3 text-slate-300 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    Pantau stok alat secara langsung
                </div>
                <div class="flex items-center gap-3 text-slate-300 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    Riwayat peminjaman tercatat rapi
                </div>
                <div class="flex items-center gap-3 text-slate-300 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    Approval petugas dalam sekali klik
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Form Login -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white mb-1.5">Selamat Datang 👋</h2>
                <p class="text-slate-400 text-sm">Masuk ke akun kamu untuk melanjutkan.</p>
            </div>

            <!-- Alert Error Session -->
            @if(session('error'))
                <div class="mb-5 flex items-start gap-2.5 p-3.5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Alert Error Validasi -->
            @if($errors->any())
                <div class="mb-5 flex items-start gap-2.5 p-3.5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Input Email -->
                <div>
                    <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Email</label>
                    <div class="input-glow flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl px-4 py-3 transition">
                        <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@email.com"
                            class="w-full bg-transparent text-white placeholder-slate-500 text-sm focus:outline-none">
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <label class="block text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Password</label>
                    <div class="input-glow flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl px-4 py-3 transition">
                        <svg class="w-5 h-5 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input type="password" name="password" id="password" required
                            placeholder="••••••••"
                            class="w-full bg-transparent text-white placeholder-slate-500 text-sm focus:outline-none">
                        <button type="button" onclick="togglePassword()" class="text-slate-500 hover:text-slate-300 transition flex-shrink-0">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-cyan-400 text-slate-950 font-bold py-3.5 rounded-xl hover:opacity-90 active:scale-[0.98] transition duration-200 shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 mt-2">
                    Masuk ke Akun
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <p class="text-center text-slate-600 text-xs mt-8">
                &copy; {{ date('Y') }} AlatKu — Sistem Peminjaman Alat
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>

</body>
</html>