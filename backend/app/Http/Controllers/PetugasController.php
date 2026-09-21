<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PetugasController extends Controller
{
    /**
     * Menampilkan daftar pengajuan peminjaman dari siswa/peminjam.
     */
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    /**
     * Menyetujui Peminjaman (Mengubah status & mengurangi stok alat).
     */
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            // Kurangi stok alat secara otomatis
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar peminjaman aktif & memperbarui status jika terdeteksi telat.
     */
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        $today = Carbon::today()->format('Y-m-d');

        // Otomatis ubah status menjadi 'telat' jika sudah melewati tgl_kembali_plan
        Peminjaman::where('status', 'dipinjam')
            ->where('tgl_kembali_plan', '<', $today)
            ->update(['status' => 'telat']);

        // Ambil peminjaman aktif dengan Eager Loading lengkap
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereIn('status', ['dipinjam', 'telat'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        // Hitung angka untuk Summary Cards
        $peminjamanAktifCount = $peminjamans->count();
        $terlambatCount = $peminjamans->where('status', 'telat')->count();
        
        $totalUnitDipinjam = $peminjamans->sum(function($item) {
            return $item->detailPinjam ? $item->detailPinjam->sum('jumlah') : 0;
        });

        return view('petugas.pengembalian.index', compact(
            'peminjamans', 
            'search', 
            'peminjamanAktifCount', 
            'terlambatCount', 
            'totalUnitDipinjam'
        ));
    }

    /**
     * Memproses Pengembalian Alat.
     */
    public function storePengembalian(Request $request, $id = null)
    {
        $request->validate([
            'peminjaman_id'   => 'nullable|exists:peminjamans,id',
            'kondisi_kembali' => 'required|string',
        ]);

        $peminjamanId = $id ?? $request->input('peminjaman_id');

        if (!$peminjamanId) {
            return redirect()->back()->with('error', 'ID Peminjaman tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($peminjamanId);
            $tglKembaliAktual = Carbon::now()->format('Y-m-d');

            // Set status peminjaman menjadi selesai
            $peminjaman->update(['status' => 'selesai']);

            // Kembalikan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            // Hitung denda otomatis berdasarkan keterlambatan
            $denda = 0;
            if (method_exists(Pengembalian::class, 'hitungDenda')) {
                $denda = Pengembalian::hitungDenda($peminjaman->tgl_kembali_plan, $tglKembaliAktual);
            } else {
                // Fallback perhitungan denda manual jika method di model belum ada (misal Rp 5.000/hari)
                $plan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
                $real = Carbon::parse($tglKembaliAktual)->startOfDay();
                if ($real->greaterThan($plan)) {
                    $denda = $plan->diffInDays($real) * 5000;
                }
            }

            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'petugas_id'      => auth()->id(),
                'tgl_kembali'     => $tglKembaliAktual,
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda'           => $denda,
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Pengembalian berhasil diproses. Denda: Rp " . number_format($denda, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Alias jika route masih memanggil 'prosesPengembalian'.
     */
    public function prosesPengembalian(Request $request, $id = null)
    {
        return $this->storePengembalian($request, $id);
    }

    /**
     * Menampilkan halaman filter laporan.
     */
    public function laporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');

        $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($dari_tanggal && $sampai_tanggal, function ($query) use ($dari_tanggal, $sampai_tanggal) {
                return $query->whereBetween('tgl_pinjam', [$dari_tanggal, $sampai_tanggal]);
            })
            ->latest()
            ->get();

        return view('petugas.laporan.index', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal'));
    }

    /**
     * Menampilkan halaman khusus cetak (print preview).
     */
    public function cetakLaporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');

        $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($dari_tanggal && $sampai_tanggal, function ($query) use ($dari_tanggal, $sampai_tanggal) {
                return $query->whereBetween('tgl_pinjam', [$dari_tanggal, $sampai_tanggal]);
            })
            ->latest()
            ->get();

        return view('petugas.laporan.cetak', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal'));
    }
}