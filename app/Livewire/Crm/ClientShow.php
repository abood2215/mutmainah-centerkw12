<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\CrmClient;
use App\Models\CrmNote;
use App\Models\CrmTask;
use App\Models\CrmActivityLog;
use App\Models\CrmConversation;

class ClientShow extends Component
{
    public $clientId;
    public $client;
    public $activeTab = 'overview';
    public $newNote = '';

    public function mount($id)
    {
        $this->clientId = $id;
        $this->loadClient();
    }

    public function loadClient()
    {
        $this->client = CrmClient::forCurrentUser()
            ->with(['notes', 'tasks', 'activityLogs', 'conversations'])
            ->findOrFail($this->clientId);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function addNote()
    {
        if (empty($this->newNote)) return;

        CrmNote::create([
            'client_id' => $this->clientId,
            'author_id' => auth()->id(),
            'content'   => $this->newNote,
        ]);

        CrmActivityLog::create([
            'client_id'    => $this->clientId,
            'performed_by' => auth()->id(),
            'action'       => 'note_added',
            'metadata'     => ['content' => $this->newNote],
        ]);

        $this->newNote = '';
        $this->loadClient();
    }

    public function render()
    {
        return view('livewire.crm.client-show')->layout('layouts.app');
    }
}
