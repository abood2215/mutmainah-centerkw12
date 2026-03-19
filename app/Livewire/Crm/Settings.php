<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Services\ChatwootService;

class Settings extends Component
{
    public $webhookUrl       = '';
    public $connectionStatus = 'idle'; // idle | success | error

    public function mount()
    {
        $this->webhookUrl = url('/webhooks/whatsapp/incoming');
    }

    public function testChatwoot()
    {
        try {
            $svc = new ChatwootService();
            $this->connectionStatus = $svc->testConnection() ? 'success' : 'error';
        } catch (\Exception $e) {
            $this->connectionStatus = 'error';
        }
    }

    public function render()
    {
        return view('livewire.crm.settings')->layout('layouts.app');
    }
}
