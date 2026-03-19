<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CrmClient;

class Clients extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStage = '';
    public $filterSource = '';
    public $filterAssignedTo = '';

    protected $queryString = ['search', 'filterStage', 'filterSource', 'filterAssignedTo'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function deleteClient($id)
    {
        $client = CrmClient::find($id);
        if ($client) {
            $client->delete();
            session()->flash('message', 'تم حذف العميل بنجاح');
        }
    }

    public function render()
    {
        $query = CrmClient::forCurrentUser()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->filterStage, fn($q) => $q->where('stage', $this->filterStage))
            ->when($this->filterSource, fn($q) => $q->where('source', $this->filterSource))
            ->when($this->filterAssignedTo, fn($q) => $q->where('assigned_to', $this->filterAssignedTo));

        return view('livewire.crm.clients', [
            'clients' => $query->orderBy('created_at', 'desc')->paginate(20)
        ])->layout('layouts.app');
    }
}
