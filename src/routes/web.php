<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::controller(FileController::class)
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/user-files/{folderPath?}', 'showFiles')
            ->where('folderPath', '(.*)')
            ->name('user-files');
        Route::get('/trash', 'trash')->name('trash');
        Route::post('/folder', 'storeFolder')->name('folder.store');
        Route::post('/file', 'store')->name('file.store');
        Route::delete('/file', 'destroy')->name('file.delete');
        Route::post('/file/restore', 'restore')->name('file.restore');
        Route::delete('/file/delete-forever', 'deleteForever')->name('file.delete-forever');
        Route::get('/file/{file}/mark-favorite', 'markFavorite')->name('file.mark-favorite');
        Route::post('/file/share', 'share')->name('file.share');
        Route::get('/file/shared-with-me', 'sharedWithMe')->name('file.shared-with-me');
        Route::get('/file/shared-by-me', 'sharedByMe')->name('file.shared-by-me');
        Route::get('/file/download', 'download')->name('file.download');
        Route::get('/file/download/shared', 'downloadShared')->name('file.download-shared');
    });

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
