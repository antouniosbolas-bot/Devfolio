<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="auth-heading">
        <p class="auth-kicker">WELCOME BACK</p>
        <h2>Sign in to your workspace</h2>
        <p>Continue building your developer story.</p>
    </div>

    <x-auth-session-status class="auth-status" :status="session('status')" />
    <x-input-error :messages="$errors->get('github')" class="auth-error" />

    <a href="{{ route('github.connect') }}" class="github-button">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 .7a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2.2c-3.3.7-4-1.4-4-1.4-.5-1.3-1.3-1.7-1.3-1.7-1.1-.8.1-.8.1-.8 1.2.1 1.8 1.2 1.8 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.7-.3-5.5-1.4-5.5-6A4.7 4.7 0 0 1 5.7 9c-.1-.3-.5-1.6.1-3.3 0 0 1-.3 3.3 1.2a11.4 11.4 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.6 1.7.2 3 .1 3.3a4.7 4.7 0 0 1 1.3 3.3c0 4.6-2.8 5.7-5.5 6 .4.3.7 1 .7 2v3c0 .3.2.7.8.6A12 12 0 0 0 12 .7"/></svg>
        <span>Continue with GitHub</span>
        <span class="github-arrow">→</span>
    </a>

    <div class="auth-divider"><span>or sign in with email</span></div>

    <form wire:submit="login" class="auth-form">
        <div class="auth-field">
            <label for="email">Email address</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="you@example.com">
            <x-input-error :messages="$errors->get('form.email')" />
        </div>
        <div class="auth-field">
            <div class="auth-label-row"><label for="password">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate>Forgot password?</a>
                @endif
            </div>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            <x-input-error :messages="$errors->get('form.password')" />
        </div>
        <label class="auth-checkbox"><input wire:model="form.remember" type="checkbox"><span>Remember me</span></label>
        <button class="auth-submit" type="submit">Sign in <span>→</span></button>
    </form>

    <p class="auth-switch">New to DevFolio? <a href="{{ route('register') }}" wire:navigate>Create a free account</a></p>
</div>
