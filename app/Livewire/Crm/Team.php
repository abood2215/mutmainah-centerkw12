<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Team extends Component
{
    public array $users = [];

    // Modal state
    public bool $showModal = false;

    // Form fields
    public string $form_name     = '';
    public string $form_gender   = 'male';
    public string $form_role     = 'agent';
    public string $form_username = '';
    public string $form_password = '';

    public function mount(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'هذه الصفحة للمدير فقط.');
        }
        $this->loadUsers();
    }

    public function loadUsers(): void
    {
        $this->users = User::orderBy('name')->get()->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'gender'       => $u->gender ?? 'male',
            'username'     => $u->username,
            'role'         => $u->role,
            'status'       => $u->status,
            'status_label' => $u->status_label,
            'status_color' => $u->status_color,
            'last_seen_at' => $u->last_seen_at?->toDateTimeString(),
        ])->values()->all();
    }

    public function openAddUser(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->reset(['form_name', 'form_username', 'form_password']);
        $this->form_gender = 'male';
        $this->form_role   = 'agent';
        $this->showModal   = true;
    }

    public function saveUser(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate([
            'form_name'     => 'required|string|max:100',
            'form_gender'   => 'required|in:male,female',
            'form_role'     => 'required|in:admin,agent',
            'form_username' => 'required|string|max:50|unique:users,username',
            'form_password' => 'required|string|min:6',
        ], [
            'form_name.required'     => 'الاسم مطلوب',
            'form_gender.required'   => 'الجنس مطلوب',
            'form_role.required'     => 'الصلاحية مطلوبة',
            'form_username.required' => 'اسم المستخدم مطلوب',
            'form_username.unique'   => 'اسم المستخدم مستخدم مسبقاً',
            'form_password.required' => 'كلمة المرور مطلوبة',
            'form_password.min'      => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        ]);

        User::create([
            'name'     => $this->form_name,
            'gender'   => $this->form_gender,
            'role'     => $this->form_role,
            'username' => $this->form_username,
            'password' => Hash::make($this->form_password),
        ]);

        $this->showModal = false;
        $this->loadUsers();

        session()->flash('success', 'تم إضافة العضو بنجاح');
    }

    public function deleteUser(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        // Prevent deleting yourself
        if ($id === auth()->id()) return;

        User::where('id', $id)->delete();
        $this->loadUsers();

        session()->flash('success', 'تم حذف العضو بنجاح');
    }

    public function render()
    {
        return view('livewire.crm.team')->layout('layouts.app');
    }
}
