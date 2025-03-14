<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveClassController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/live-class/{id}', [LiveClassController::class, 'show'])->name('live-class.show');
    Route::post('/live-class', [LiveClassController::class, 'store'])->name('live-class.store');
    Route::post('/live-class/{id}/join', [LiveClassController::class, 'join'])->name('live-class.join');
    Route::post('/live-class/{id}/end', [LiveClassController::class, 'end'])->name('live-class.end');
});

// Route for shared readlists - this creates a nice URL for sharing
Route::get('/readlists/shared/{shareKey}', function($shareKey) {
    // This route will be handled by the frontend
    return view('welcome');
})->name('readlist.shared');