<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pengembalian extends Model
{
    protected $table = 'pengembalian';

    protected $fillable = [
        'peminjaman_id', 
        'tgl_kembali', 
        'kondisi_kembali', 
        'denda', 
        'petugas_id'
    ];

    protected function casts(): array
    {
        return [
            'tgl_kembali' => 'date:Y-m-d',
            'denda' => 'integer',
        ];
    }

    /**
     * Relasi ke model Peminjaman
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    /**
     * Relasi ke model User (Petugas)
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Hitung denda keterlambatan berdasarkan tarif per hari.
     * Tarif diambil dari config('denda.per_hari'), default Rp 2.000/hari.
     */
    public static function hitungDenda(?string $tglKembaliPlan, ?string $tglKembaliAktual): int
    {
        // Jika tanggal rencana pengembalian kosong, denda dihitung 0
        if (!$tglKembaliPlan || !$tglKembaliAktual) {
            return 0;
        }

        $tarifPerHari = config('denda.per_hari', 2000);

        $plan = Carbon::parse($tglKembaliPlan)->startOfDay();
        $aktual = Carbon::parse($tglKembaliAktual)->startOfDay();

        // Hitung selisih hari
        $selisihHari = $plan->diffInDays($aktual, false);

        // Jika selisih hari > 0 (artinya tanggal aktual lewat dari rencana)
        return $selisihHari > 0 ? (int) ($selisihHari * $tarifPerHari) : 0;
    }
}