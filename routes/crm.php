<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Crm\Pipeline;
use App\Livewire\Crm\Clients;
use App\Livewire\Crm\ClientShow;
use App\Livewire\Crm\Tasks;
use App\Livewire\Crm\Campaigns;
use App\Livewire\Crm\Inbox;
use App\Livewire\Crm\Settings;
use App\Livewire\Crm\Team;
use App\Livewire\Crm\CannedResponses;
use App\Livewire\Crm\BusinessHours;
use App\Http\Controllers\ChatwootWebhookController;

/*
|--------------------------------------------------------------------------
| CRM Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('crm')->group(function () {
    Route::get('/pipeline', Pipeline::class)->name('crm.pipeline');
    Route::get('/clients', Clients::class)->name('crm.clients');
    Route::get('/clients/{id}', ClientShow::class)->name('crm.client-show');
    Route::get('/tasks', Tasks::class)->name('crm.tasks');
    Route::get('/campaigns', Campaigns::class)->name('crm.campaigns');
    Route::get('/inbox', Inbox::class)->name('crm.inbox');
    Route::get('/settings', Settings::class)->name('crm.settings');

    // Phase 5: Team & Tools
    Route::get('/team', Team::class)->name('crm.team');
    Route::get('/canned-responses', CannedResponses::class)->name('crm.canned-responses');
    Route::get('/settings/business-hours', BusinessHours::class)->name('crm.business-hours');
});

/*
|--------------------------------------------------------------------------
| Webhooks (no auth required)
|--------------------------------------------------------------------------
*/

// Chatwoot incoming webhook (message_created events)
Route::post('/webhooks/chatwoot', [ChatwootWebhookController::class, 'handle'])
    ->name('webhooks.chatwoot');
