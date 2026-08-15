<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicQuoteController;

Route::redirect('/', '/admin');

Route::get('/quote/{token}', [PublicQuoteController::class, 'show'])->name('public.quote.show');
Route::post('/quote/{token}/accept', [PublicQuoteController::class, 'accept'])->name('public.quote.accept');
Route::post('/quote/{token}/revise', [PublicQuoteController::class, 'revise'])->name('public.quote.revise');
Route::get('/sample/{token}', [PublicQuoteController::class, 'showSample'])->name('public.sample.show');
