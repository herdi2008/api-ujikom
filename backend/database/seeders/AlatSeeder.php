<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class AlatSeeder extends Seeder
{
    public function run(): void
    {
        $alat = [
            [
                'kategori' => 'Jaringan & Konektivitas',
                'nama_alat' => 'Router Mikrotik RB941-2nD',
                'stok' => 15,
                'status_kondisi' => 'Baik',
                'deskripsi' => 'Router nirkabel rumahan yang cocok untuk praktik jaringan dasar.',
                'gambar' => 'mikrotik_rb941.jpg',
            ],
            [
                'kategori' => 'Multimedia & Audio Visual',
                'nama_alat' => 'Kamera DSLR Canon EOS 3000D',
                'stok' => 5,
                'status_kondisi' => 'Baik',
                'deskripsi' => 'Kamera pemula untuk kebutuhan dokumentasi dan pembuatan aset media.',
                'gambar' => 'canon_3000d.jpg',
            ],
            [
                'kategori' => 'Perangkat Pemrosesan',
                'nama_alat' => 'Mini PC Intel NUC 11',
                'stok' => 8,
                'status_kondisi' => 'Baik',
                'deskripsi' => 'Perangkat komputasi ringkas untuk server lokal skala kecil.',
                'gambar' => 'intel_nuc.jpg',
            ],
            [
                'kategori' => 'Perkakas & Elektronik',
                'nama_alat' => 'Tang Crimping RJ45/RJ11 Proskit',
                'stok' => 20,
                'status_kondisi' => 'Baik',
                'deskripsi' => 'Alat potong dan pasang konektor kabel UTP.',
                'gambar' => 'crimping_proskit.jpg',
            ],
            [
                'kategori' => 'Suku Cadang & Aksesoris',
                'nama_alat' => 'Adapter HDMI to VGA dengan Audio',
                'stok' => 25,
                'status_kondisi' => 'Baik',
                'deskripsi' => 'Konverter display untuk menyambungkan perangkat modern ke proyektor lama.',
                'gambar' => 'hdmi_vga.jpg',
            ],
        ];

        foreach ($alat as $item) {
            $kategoriId = Kategori::where('nama_kategori', $item['kategori'])->value('id');

            if (!$kategoriId) {
                $this->command->warn("Kategori '{$item['kategori']}' tidak ditemukan, alat '{$item['nama_alat']}' dilewati.");
                continue;
            }

            Alat::firstOrCreate(
                ['nama_alat' => $item['nama_alat']],
                [
                    'kategori_id' => $kategoriId,
                    'stok' => $item['stok'],
                    'status_kondisi' => $item['status_kondisi'],
                    'deskripsi' => $item['deskripsi'],
                    'gambar' => $item['gambar'],
                ]
            );
        }
    }
}