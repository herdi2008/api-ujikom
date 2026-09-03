<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Langsung ubah tipe kolom status di database MySQL
        DB::statement("ALTER TABLE peminjaman MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'diajukan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE peminjaman MODIFY COLUMN status ENUM('diajukan', 'dipinjam', 'telat') NOT NULL DEFAULT 'diajukan'");
    }
};