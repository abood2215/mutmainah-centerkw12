<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Livewire\Auth\Login;

Route::get('/', function () {
    return redirect()->route('crm.pipeline');
});

// Auth Routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// ===== WhatsApp Cloud API Webhooks (CSRF Excluded in bootstrap/app.php) =====
Route::get('/webhooks/whatsapp/verify',   [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp/incoming',[WhatsAppWebhookController::class, 'incoming']);
