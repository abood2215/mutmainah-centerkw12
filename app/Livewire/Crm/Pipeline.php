<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\CrmClient;
use App\Models\CrmActivityLog;

class Pipeline extends Component
{
    public $search = '';
    public $clientsByStage = [];

    public function mount()
    {
        $this->loadClients();
    }

    public function updatedSearch()
    {
        $this->loadClients();
    }

    public function loadClients()
    {
        $stages = array_keys(CrmClient::getStages());
        $this->clientsByStage = [];

        foreach ($stages as $stage) {
            $this->clientsByStage[$stage] = CrmClient::forCurrentUser()
                ->where('stage', $stage)
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function moveStage($clientId, $newStage)
    {
        $validStages = array_keys(CrmClient::getStages());
        if (!in_array($newStage, $validStages)) return;

        $client = CrmClient::forCurrentUser()->where('id', $clientId)->first();
        if ($client) {
            $oldStage = $client->stage;
            $client->stage = $newStage;
            $client->save();

            CrmActivityLog::create([
                'client_id'    => $client->id,
                'performed_by' => auth()->id(),
                'action'       => 'stage_changed',
                'metadata'     => ['from' => $oldStage, 'to' => $newStage],
            ]);

            $this->loadClients();
        }
    }

    public function render()
    {
        return view('livewire.crm.pipeline', [
            'stages' => CrmClient::getStages()
        ])->layout('layouts.app');
    }
}
