<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // Tarif denda per hari keterlambatan
    const TARIF_DENDA_PER_HARI = 5000;

    // ===================== DASHBOARD =====================

    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();
        $totalAlat = Alat::count();

        // Menghitung status 'diajukan', 'dipinjam', dan 'telat'
        $peminjamanAktif = Peminjaman::whereIn('status', ['diajukan', 'dipinjam', 'telat'])->count();

        // Diperbarui agar otomatis menghitung data pengembalian khusus bulan dan tahun berjalan
        $pengembalianBulanIni = Pengembalian::whereMonth('tgl_kembali', Carbon::now()->month)
                                    ->whereYear('tgl_kembali', Carbon::now()->year)
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
                $query->where(function ($q) use ($search) {
                    $q->where('nama_alat', 'like', "%{$search}%")
                        ->orWhere('status_kondisi', 'like', "%{$search}%")
                        ->orWhereHas('kategori', function ($k) use ($search) {
                            $k->where('nama_kategori', 'like', "%{$search}%");
                        });
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
        $data = $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->simpanGambar($request->file('gambar'));
        }

        Alat::create($data);

        $this->catatLog('Menambahkan alat baru: ' . $data['nama_alat']);

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

        $data = $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $this->hapusGambar($alat->gambar);
            $data['gambar'] = $this->simpanGambar($request->file('gambar'));
        }

        $alat->update($data);

        $this->catatLog('Mengubah data alat: ' . $alat->nama_alat);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        if (DetailPinjam::where('alat_id', $alat->id)->exists()) {
            return redirect()->route('admin.alat.index')
                ->with('error', 'Alat tidak dapat dihapus karena sudah tercatat di data peminjaman.');
        }

        $namaAlat = $alat->nama_alat;

        $this->hapusGambar($alat->gambar);
        $alat->delete();

        $this->catatLog('Menghapus alat: ' . $namaAlat);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // ===================== CRUD USER =====================

    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
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
            'no_hp' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        $this->catatLog('Menambahkan user baru: ' . $request->name . ' (' . $request->role . ')');

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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => 'required|in:admin,petugas,peminjam',
            'no_hp' => 'nullable|string|max:20',
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

        $this->catatLog('Mengubah data user: ' . $user->name);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Kamu tidak dapat menghapus akun yang sedang digunakan.');
        }

        $namaUser = $user->name;

        $user->delete();

        $this->catatLog('Menghapus user: ' . $namaUser);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // ===================== CRUD KATEGORI =====================

    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            $query->where('nama_kategori', 'like', "%{$search}%");
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

        $this->catatLog('Menambahkan kategori baru: ' . $request->nama_kategori);

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
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori', 'nama_kategori')->ignore($kategori->id),
            ],
        ]);

        $namaLama = $kategori->nama_kategori;

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        $this->catatLog("Mengubah kategori: {$namaLama} menjadi {$request->nama_kategori}");

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat()->exists()) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $kategori->delete();

        $this->catatLog('Menghapus kategori: ' . $kategori->nama_kategori);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // ===================== CRUD PEMINJAMAN =====================

    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('status', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%");
                        });
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
            'alat_id.*' => 'distinct|exists:alat,id',
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
                $jumlahPinjam = (int) $request->jumlah[$index];
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

            $this->catatLog('Mengajukan peminjaman baru (ID #' . $peminjaman->id . ') untuk user ID: ' . $request->user_id);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $request->merge(['status' => strtolower((string) $request->input('status'))]);

        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,selesai,telat,dikembali,dikembalikan',
        ]);

        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        DB::beginTransaction();
        try {
            $statusLama = strtolower($peminjaman->status);
            $statusBaru = $request->status;

            if (in_array($statusBaru, ['dikembali', 'dikembalikan'])) {
                $statusBaru = 'selesai';
            }

            if ($statusLama !== 'dipinjam' && $statusBaru === 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = $detail->alat;
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi untuk dipinjam.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            } elseif ($statusLama === 'dipinjam' && $statusBaru === 'selesai') {
                $denda = $this->hitungDenda($peminjaman->tgl_kembali_plan, now());

                if ($denda > 0) {
                    $statusBaru = 'telat';
                }

                Pengembalian::updateOrCreate(
                    ['peminjaman_id' => $peminjaman->id],
                    [
                        'tgl_kembali' => now()->toDateString(),
                        'kondisi_kembali' => 'Baik',
                        'denda' => $denda,
                        'petugas_id' => auth()->id(),
                    ]
                );

                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            $this->catatLog("Mengubah status peminjaman #{$peminjaman->id} dari '{$statusLama}' menjadi '{$statusBaru}'");

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        DB::transaction(function () use ($peminjaman) {
            if (strtolower($peminjaman->status) === 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->delete();
        });

        $this->catatLog("Menghapus data peminjaman #{$peminjaman->id}");

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // ===================== KELOLA PENGEMBALIAN =====================

    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        $bulan = $request->input('bulan');

        $pengembalians = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kondisi_kembali', 'like', "%{$search}%")
                        ->orWhereHas('peminjaman', function ($p) use ($search) {
                            $p->where('status', 'like', "%{$search}%");
                        })
                        ->orWhereHas('peminjaman.user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('petugas', function ($t) use ($search) {
                            $t->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan), function ($query) use ($bulan) {
                [$year, $month] = explode('-', $bulan);
                $query->whereYear('tgl_kembali', $year)
                    ->whereMonth('tgl_kembali', $month);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $peminjamanAktifCount = Peminjaman::whereIn('status', ['dipinjam', 'telat'])->count();

        $totalUnitDipinjam = DetailPinjam::whereHas('peminjaman', function ($q) {
            $q->whereIn('status', ['dipinjam', 'telat']);
        })->sum('jumlah');

        $terlambatCount = Peminjaman::where('status', 'telat')->count();

        return view('admin.pengembalian.index', compact(
            'pengembalians', 
            'search', 
            'bulan', 
            'peminjamanAktifCount',
            'totalUnitDipinjam', 
            'terlambatCount'
        ));
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
            $dendaOtomatis = $this->hitungDenda($peminjaman->tgl_kembali_plan, $request->tgl_kembali);
            $statusAkhir = $dendaOtomatis > 0 ? 'telat' : 'selesai';
            $totalDenda = $dendaOtomatis + (int) ($request->denda ?? 0);

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

            $this->catatLog(
                "Memproses pengembalian peminjaman ID: #{$peminjaman->id} dengan status akhir: {$statusAkhir} dan denda: Rp"
                . number_format($totalDenda, 0, ',', '.')
            );

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Pengembalian berhasil diproses.');
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
        $pengembalian = Pengembalian::findOrFail($id);

        $request->validate([
            'kondisi_kembali' => 'required|string|max:255',
            'denda' => 'required|integer|min:0',
        ]);

        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda,
        ]);

        $this->catatLog("Mengoreksi data pengembalian untuk peminjaman ID: #{$pengembalian->peminjaman_id}.");

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

            $this->catatLog("Membatalkan data pengembalian untuk peminjaman ID: #{$peminjaman->id}, status dikembalikan ke 'dipinjam'.");

            DB::commit();
            return redirect()->route('admin.pengembalian.index')->with('success', 'Data pengembalian berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ===================== HELPER =====================

    private function hitungDenda($tglPlan, $tglKembali): int
    {
        $plan = Carbon::parse($tglPlan)->startOfDay();
        $real = Carbon::parse($tglKembali)->startOfDay();

        if ($real->lessThanOrEqualTo($plan)) {
            return 0;
        }

        $selisihHari = (int) $plan->diffInDays($real);

        return $selisihHari * self::TARIF_DENDA_PER_HARI;
    }

    private function catatLog(string $aktivitas): void
    {
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => $aktivitas,
        ]);
    }

    private function simpanGambar($file): string
    {
        $namaFile = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('storage/alat'), $namaFile);

        return 'storage/alat/' . $namaFile;
    }

    private function hapusGambar(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
    }
}