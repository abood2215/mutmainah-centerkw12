<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool   $remember = false;
    public string $error    = '';

    public function login()
    {
        $this->error = '';

        if (empty($this->username) || empty($this->password)) {
            $this->error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
            return;
        }

        if (Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended(route('crm.pipeline'));
        }

        $this->error    = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest');
    }
}
