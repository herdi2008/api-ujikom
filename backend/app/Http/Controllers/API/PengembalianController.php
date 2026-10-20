<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pengembalian extends Model
{
    protected $table = 'pengembalian';

    protected $fillable = [
        'peminjaman_id', 'tgl_kembali', 'kondisi_kembali', 'denda', 'petugas_id'
    ];

    protected function casts(): array
    {
        return [
            'tgl_kembali' => 'date:Y-m-d',
        ];
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Hitung denda keterlambatan berdasarkan tarif per hari.
     * Tarif diambil dari config('denda.per_hari'), default Rp 2.000/hari.
     */
    public static function hitungDenda(string $tglKembaliPlan, string $tglKembaliAktual): int
    {
        $tarifPerHari = config('denda.per_hari', 2000);

        $plan = Carbon::parse($tglKembaliPlan)->startOfDay();
        $aktual = Carbon::parse($tglKembaliAktual)->startOfDay();

        $selisihHari = $plan->diffInDays($aktual, false); // false = boleh negatif kalau lebih cepat

        return $selisihHari > 0 ? $selisihHari * $tarifPerHari : 0;
    }
}