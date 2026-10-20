<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Alat extends Model
{
    protected $table = 'alat';

    protected $fillable = [
        'kategori_id', 'nama_alat', 'stok', 'status_kondisi', 'deskripsi', 'gambar'
    ];

    protected function casts(): array {
        return [
            'stok' => 'integer',
        ];
    }

    public function kategori(): BelongsTo {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPinjam(): HasMany {
        return $this->hasMany(DetailPinjam::class);
    }

    // Scope: hanya alat yang stoknya masih ada
    public function scopeTersedia(Builder $query): Builder {
        return $query->where('stok', '>', 0);
    }
}