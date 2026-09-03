<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KategoriController;

use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\API\AlatController;

use App\Http\Controllers\API\UserController;

use App\Http\Controllers\API\PeminjamanController;

use App\Http\Controllers\API\PengembalianController;

use App\Http\Controllers\API\LogAktivitasController;

use App\Http\Controllers\API\LaporanController;



/*

|--------------------------------------------------------------------------

| Public Routes (Tidak perlu token)

|--------------------------------------------------------------------------

*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);



/*

|--------------------------------------------------------------------------

| Protected Routes (Wajib membawa Bearer Token dari Sanctum)

|--------------------------------------------------------------------------

*/

Route::middleware('auth:sanctum')->group(function () {



    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);



    /*

    |----------------------------------------------------------------------

    | Hanya Admin

    |----------------------------------------------------------------------

    */

    Route::middleware('role.admin')->group(function () {

        Route::apiResource('kategori', KategoriController::class);

        Route::apiResource('alat', AlatController::class);

        Route::get('/katalog', [AlatController::class, 'katalog']);

        Route::apiResource('users', UserController::class);



        // Peminjaman — admin bisa lihat semua, update, & hapus

        Route::get('/peminjaman', [PeminjamanController::class, 'index']);

        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);

        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);

        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);



        // Pengembalian — admin bisa lihat semua, update, & hapus

        Route::get('/pengembalian', [PengembalianController::class, 'index']);

        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show']);

        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update']);

        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);



        // Log Aktivitas — hanya admin yang boleh lihat riwayat aktivitas sistem

        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);



        // Laporan Peminjaman

        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);

    });



    /*

    |----------------------------------------------------------------------

    | Hanya Petugas

    |----------------------------------------------------------------------

    */

    Route::middleware('role.petugas')->group(function () {

        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);

        Route::post('/pengembalian', [PengembalianController::class, 'store']);



        // Laporan Peminjaman

        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);

    });



    /*

    |----------------------------------------------------------------------

    | Hanya Peminjam

    |----------------------------------------------------------------------

    */

    Route::middleware('role.peminjam')->group(function () {

        Route::post('/peminjaman', [PeminjamanController::class, 'store']);

        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);

    });

});