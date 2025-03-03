<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::post('/submit-message', [ChatController::class, 'submitMessage'])->name('chat.submit');
Route::get('/clear-chat', [ChatController::class, 'clearChat'])->name('chat.clear');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
