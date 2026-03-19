<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/', function () {
    return redirect()->route('crm.pipeline');
});

// ===== WhatsApp Cloud API Webhooks (CSRF Excluded in bootstrap/app.php) =====
Route::get('/webhooks/whatsapp/verify',   [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp/incoming',[WhatsAppWebhookController::class, 'incoming']);
