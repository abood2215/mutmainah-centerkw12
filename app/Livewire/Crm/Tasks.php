<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\CrmTask;

class Tasks extends Component
{
    public $filterStatus = '';
    public $filterPriority = '';
    public $filterAssignedTo = '';

    public function render()
    {
        $query = CrmTask::forCurrentUser()
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterAssignedTo, fn($q) => $q->where('assigned_to', $this->filterAssignedTo));

        // Stats (scoped to current user)
        $stats = [
            'pending'    => CrmTask::forCurrentUser()->where('status', 'pending')->count(),
            'inprogress' => CrmTask::forCurrentUser()->where('status', 'inprogress')->count(),
            'done'       => CrmTask::forCurrentUser()->where('status', 'done')->count(),
            'overdue'    => CrmTask::forCurrentUser()->where('status', '!=', 'done')->where('due_date', '<', now())->count(),
        ];

        // Database-agnostic sorting (SQLite & MySQL compatible)
        $tasks = $query->orderByRaw("CASE status WHEN 'inprogress' THEN 1 WHEN 'pending' THEN 2 WHEN 'done' THEN 3 END")
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('livewire.crm.tasks', [
            'tasks' => $tasks,
            'stats' => $stats
        ])->layout('layouts.app');
    }
}
