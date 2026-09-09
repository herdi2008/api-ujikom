<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PeminjamController extends Controller
{
    // Menampilkan katalog alat yang tersedia untuk dipinjam
    public function katalogAlat(Request $request)
    {
        $search = $request->input('search');
        $kategori_id = $request->input('kategori_id');

        $alats = Alat::with('kategori')
            ->tersedia()
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%");
            })
            ->when($kategori_id, function ($query, $kategori_id) {
                return $query->where('kategori_id', $kategori_id);
            })
            ->orderBy('nama_alat')
            ->get();

        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('peminjam.katalog', compact('alats', 'kategoris', 'search', 'kategori_id'));
    }

    // Memproses pengajuan peminjaman (bisa banyak alat sekaligus)
    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array|min:1',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ], [
            'alat_id.required' => 'Pilih minimal satu alat untuk diajukan.',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => Auth::id(),
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlah = $request->jumlah[$index] ?? 1;
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlah) {
                    throw new \Exception("Stok {$alat->nama_alat} tidak mencukupi (tersisa {$alat->stok}).");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlah,
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim, menunggu persetujuan petugas.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // Menampilkan riwayat peminjaman milik user yang login
    public function riwayatPeminjaman(Request $request)
    {
        $peminjamans = Peminjaman::with(['detailPinjam.alat', 'pengembalian'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    // Peminjam menandai bahwa alat sudah dikembalikan secara fisik (menunggu verifikasi petugas)
    public function ajukanPengembalian($id)
    {
        $peminjaman = Peminjaman::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
            return redirect()->back()->with('error', 'Peminjaman ini tidak bisa diajukan pengembalian.');
        }

        $peminjaman->update(['status' => 'dikembalikan']);

        return redirect()->back()->with('success', 'Pengembalian diajukan, menunggu verifikasi petugas.');
    }
}