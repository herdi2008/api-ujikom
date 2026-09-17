<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // Constant Tarif Denda Per Hari
    const TARIF_DENDA_PER_HARI = 5000;

    // Menampilkan Dashboard Admin & Log Aktivitas
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();
        $totalAlat = Alat::count();
        $peminjamanAktif = Peminjaman::where('status', 'dipinjam')->count();

        $pengembalianBulanIni = Pengembalian::whereMonth('tgl_kembali', now()->month)
            ->whereYear('tgl_kembali', now()->year)
            ->count();

        $totalUser = User::count();

        return view('admin.dashboard', compact(
            'logs',
            'totalAlat',
            'peminjamanAktif',
            'pengembalianBulanIni',
            'totalUser'
        ));
    }

    // ===================== CRUD ALAT =====================

    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);

        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menambahkan alat baru: ' . $request->nama_alat,
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // ===================== CRUD USER =====================

    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User dihapus.');
    }

    // ===================== CRUD KATEGORI =====================

    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alats()->count() > 0) {
            return redirect()->route('admin.kategori.index')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // ===================== CRUD PEMINJAMAN =====================

    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,selesai,telat,dikembali,dikembalikan,Diajukan,Dipinjam,Selesai,Telat,Dikembali,Dikembalikan',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = strtolower($peminjaman->status);
            $statusBaru = strtolower($request->status);

            if (in_array($statusBaru, ['dikembali', 'dikembalikan'])) {
                $statusBaru = 'selesai';
            }

            // 1. Jika diubah ke "Dipinjam" (Stok berkurang)
            if ($statusLama != 'dipinjam' && $statusBaru == 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = $detail->alat;
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi untuk dipinjam.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            } 
            // 2. Jika diubah ke "Selesai" / "Dikembalikan" (Hitung denda otomatis & stok kembali)
            elseif ($statusLama == 'dipinjam' && ($statusBaru == 'selesai' || $statusBaru == 'dikembalikan')) {
                $tglHariIni = Carbon::now()->startOfDay();
                $tglPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();

                $dendaOtomatis = 0;
                if ($tglHariIni->greaterThan($tglPlan)) {
                    $selisihHari = $tglPlan->diffInDays($tglHariIni);
                    $dendaOtomatis = $selisihHari * self::TARIF_DENDA_PER_HARI;
                    $statusBaru = 'telat'; // Ubah status ke telat jika melewati deadline
                }

                // Buat/update record pengembalian
                Pengembalian::updateOrCreate(
                    ['peminjaman_id' => $peminjaman->id],
                    [
                        'tgl_kembali' => now()->toDateString(),
                        'kondisi_kembali' => 'Baik',
                        'denda' => $dendaOtomatis,
                        'petugas_id' => auth()->id(),
                    ]
                );

                // Kembalikan stok alat
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

        if ($peminjaman->status == 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }
        }

        $peminjaman->delete();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // ===================== KELOLA PENGEMBALIAN =====================

    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        $bulan = $request->input('bulan');

        $pengembalians = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas'])
            ->when($search, function ($query, $search) {
                return $query->where('kondisi_kembali', 'like', "%{$search}%")
                    ->orWhereHas('peminjaman', function ($q) use ($search) {
                        $q->where('status', 'like', "%{$search}%");
                    })
                    ->orWhereHas('peminjaman.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('petugas', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($bulan, function ($query) use ($bulan) {
                [$year, $month] = explode('-', $bulan);
                return $query->whereMonth('tgl_kembali', $month)
                    ->whereYear('tgl_kembali', $year);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengembalian.index', compact('pengembalians', 'search', 'bulan'));
    }

    public function createPengembalian()
    {
        $peminjamans = Peminjaman::with('user')
            ->where('status', 'dipinjam')
            ->latest()
            ->get();

        return view('admin.pengembalian.create', compact('peminjamans'));
    }

    public function storePengembalian(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'tgl_kembali' => 'required|date',
            'kondisi_kembali' => 'required|string|max:255',
            'denda' => 'nullable|integer|min:0',
        ]);

        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($request->peminjaman_id);

        if ($peminjaman->status !== 'dipinjam') {
            return back()->withInput()->with('error', 'Peminjaman ini sudah tidak berstatus dipinjam.');
        }

        DB::beginTransaction();
        try {
            $tglPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $tglReal = Carbon::parse($request->tgl_kembali)->startOfDay();

            $dendaOtomatis = 0;
            $statusAkhir = 'selesai';

            if ($tglReal->greaterThan($tglPlan)) {
                $selisihHari = $tglPlan->diffInDays($tglReal);
                $dendaOtomatis = $selisihHari * self::TARIF_DENDA_PER_HARI;
                $statusAkhir = 'telat';
            }

            $totalDenda = $dendaOtomatis + ($request->denda ?? 0);

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $request->tgl_kembali,
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $totalDenda,
                'petugas_id' => auth()->id(),
            ]);

            $peminjaman->update(['status' => $statusAkhir]);

            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => "Memproses pengembalian peminjaman ID: #{$peminjaman->id} dengan status akhir: {$statusAkhir} dan denda: Rp" . number_format($totalDenda, 0, ',', '.'),
            ]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Pengembalian berhasil dipproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function editPengembalian($id)
    {
        $pengembalian = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat'])->findOrFail($id);
        return view('admin.pengembalian.edit', compact('pengembalian'));
    }

    public function updatePengembalian(Request $request, $id)
    {
        $pengembalian = Pengembalian::with('peminjaman')->findOrFail($id);

        $request->validate([
            'kondisi_kembali' => 'required|string|max:255',
            'denda' => 'required|integer|min:0',
        ]);

        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda,
        ]);

        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => "Mengoreksi data pengembalian untuk peminjaman ID: #{$pengembalian->peminjaman_id}.",
        ]);

        return redirect()->route('admin.pengembalian.index')->with('success', 'Data pengembalian berhasil diperbarui.');
    }

    public function destroyPengembalian($id)
    {
        $pengembalian = Pengembalian::with('peminjaman.detailPinjam.alat')->findOrFail($id);
        $peminjaman = $pengembalian->peminjaman;

        DB::beginTransaction();
        try {
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = $detail->alat;
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi untuk dibatalkan (kemungkinan sudah dipinjam ulang).");
                }
                $alat->decrement('stok', $detail->jumlah);
            }

            $peminjaman->update(['status' => 'dipinjam']);
            $pengembalian->delete();

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => "Membatalkan data pengembalian untuk peminjaman ID: #{$peminjaman->id}, status dikembalikan ke 'dipinjam'.",
            ]);

            DB::commit();
            return redirect()->route('admin.pengembalian.index')->with('success', 'Data pengembalian berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}