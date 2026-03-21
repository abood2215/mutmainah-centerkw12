<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Crm\Pipeline;
use App\Livewire\Crm\Clients;
use App\Livewire\Crm\ClientShow;
use App\Livewire\Crm\Tasks;
use App\Livewire\Crm\Campaigns;
use App\Livewire\Crm\Inbox;
use App\Livewire\Crm\Settings;

/*
|--------------------------------------------------------------------------
| CRM Routes (Phase 1-4 Complete)
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
});
