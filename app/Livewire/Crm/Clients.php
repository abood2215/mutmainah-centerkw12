<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CrmClient;
use App\Models\CrmActivityLog;

class Clients extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStage = '';
    public $filterSource = '';
    public $filterAssignedTo = '';

    // New client modal
    public $showModal = false;
    public $newName = '';
    public $newPhone = '';
    public $newEmail = '';
    public $newSource = 'whatsapp';
    public $newStage = 'new';
    public $newPriority = 'medium';
    public $newDealValue = 0;
    public $newNotes = '';

    protected $queryString = ['search', 'filterStage', 'filterSource', 'filterAssignedTo'];

    protected $rules = [
        'newName'      => 'required|string|max:255',
        'newPhone'     => 'nullable|string|max:30',
        'newEmail'     => 'nullable|email|max:255',
        'newSource'    => 'required|in:whatsapp,instagram,referral,direct,website',
        'newStage'     => 'required|in:new,contacted,interested,booked,active,followup,completed',
        'newPriority'  => 'required|in:low,medium,high',
        'newDealValue' => 'nullable|numeric|min:0',
        'newNotes'     => 'nullable|string',
    ];

    protected $messages = [
        'newName.required' => 'اسم العميل مطلوب',
        'newEmail.email'   => 'البريد الإلكتروني غير صحيح',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->newName = '';
        $this->newPhone = '';
        $this->newEmail = '';
        $this->newSource = 'whatsapp';
        $this->newStage = 'new';
        $this->newPriority = 'medium';
        $this->newDealValue = 0;
        $this->newNotes = '';
        $this->resetValidation();
    }

    public function saveClient()
    {
        $this->validate();

        $client = CrmClient::create([
            'name'       => $this->newName,
            'phone'      => $this->newPhone ?: null,
            'email'      => $this->newEmail ?: null,
            'source'     => $this->newSource,
            'stage'      => $this->newStage,
            'priority'   => $this->newPriority,
            'deal_value' => $this->newDealValue ?: 0,
            'notes'      => $this->newNotes ?: null,
            'assigned_to'=> auth()->id() ?? 1,
        ]);

        CrmActivityLog::create([
            'client_id'    => $client->id,
            'performed_by' => auth()->id() ?? 1,
            'action'       => 'client_created',
            'metadata'     => ['source' => $this->newSource],
        ]);

        $this->closeModal();
        session()->flash('message', 'تم إضافة العميل بنجاح');
    }

    public function deleteClient($id)
    {
        $client = CrmClient::find($id);
        if ($client) {
            $client->delete();
            session()->flash('message', 'تم حذف العميل بنجاح');
        }
    }

    public function export()
    {
        $clients = CrmClient::forCurrentUser()
            ->when($this->filterStage, fn($q) => $q->where('stage', $this->filterStage))
            ->when($this->filterSource, fn($q) => $q->where('source', $this->filterSource))
            ->when($this->filterAssignedTo, fn($q) => $q->where('assigned_to', $this->filterAssignedTo))
            ->orderBy('created_at', 'desc')
            ->get();

        $stages = CrmClient::getStages();

        return response()->streamDownload(function () use ($clients, $stages) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['الاسم', 'الهاتف', 'البريد الإلكتروني', 'المرحلة', 'المصدر', 'الأولوية', 'القيمة (KD)', 'تاريخ الإضافة']);

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->name,
                    $client->phone ?? '',
                    $client->email ?? '',
                    $stages[$client->stage]['name'] ?? $client->stage,
                    $client->source,
                    $client->priority,
                    $client->deal_value,
                    $client->created_at->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, 'clients-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $query = CrmClient::forCurrentUser()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStage, fn($q) => $q->where('stage', $this->filterStage))
            ->when($this->filterSource, fn($q) => $q->where('source', $this->filterSource))
            ->when($this->filterAssignedTo, fn($q) => $q->where('assigned_to', $this->filterAssignedTo));

        return view('livewire.crm.clients', [
            'clients' => $query->orderBy('created_at', 'desc')->paginate(20)
        ])->layout('layouts.app');
    }
}
