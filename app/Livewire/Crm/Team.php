<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\User;

class Team extends Component
{
    public array $users = [];

    public function mount(): void
    {
        $this->loadUsers();
    }

    public function loadUsers(): void
    {
        $this->users = User::orderBy('name')->get()->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'username'     => $u->username,
            'role'         => $u->role,
            'status'       => $u->status,
            'status_label' => $u->status_label,
            'status_color' => $u->status_color,
            'last_seen_at' => $u->last_seen_at?->toDateTimeString(),
        ])->values()->all();
    }

    public function render()
    {
        return view('livewire.crm.team')->layout('layouts.app');
    }
}
