<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        event(new Registered($user = User::create($validated)));
        Auth::login($user);
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="auth-heading">
        <p class="auth-kicker">START BUILDING</p>
        <h2>Create your account</h2>
        <p>One beautiful home for everything you build.</p>
    </div>
    <a href="{{ route('github.connect') }}" class="github-button">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 .7a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2.2c-3.3.7-4-1.4-4-1.4-.5-1.3-1.3-1.7-1.3-1.7-1.1-.8.1-.8.1-.8 1.2.1 1.8 1.2 1.8 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.7-.3-5.5-1.4-5.5-6A4.7 4.7 0 0 1 5.7 9c-.1-.3-.5-1.6.1-3.3 0 0 1-.3 3.3 1.2a11.4 11.4 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.6 1.7.2 3 .1 3.3a4.7 4.7 0 0 1 1.3 3.3c0 4.6-2.8 5.7-5.5 6 .4.3.7 1 .7 2v3c0 .3.2.7.8.6A12 12 0 0 0 12 .7"/></svg>
        <span>Sign up with GitHub</span><span class="github-arrow">→</span>
    </a>
    <div class="auth-divider"><span>or use your email</span></div>
    <form wire:submit="register" class="auth-form">
        <div class="auth-field"><label for="name">Full name</label><input wire:model="name" id="name" type="text" required autofocus autocomplete="name" placeholder="Alex Morgan"><x-input-error :messages="$errors->get('name')" /></div>
        <div class="auth-field"><label for="email">Email address</label><input wire:model="email" id="email" type="email" required autocomplete="username" placeholder="you@example.com"><x-input-error :messages="$errors->get('email')" /></div>
        <div class="auth-field"><label for="password">Password</label><input wire:model="password" id="password" type="password" required autocomplete="new-password" placeholder="At least 8 characters"><x-input-error :messages="$errors->get('password')" /></div>
        <div class="auth-field"><label for="password_confirmation">Confirm password</label><input wire:model="password_confirmation" id="password_confirmation" type="password" required autocomplete="new-password" placeholder="Repeat your password"><x-input-error :messages="$errors->get('password_confirmation')" /></div>
        <button class="auth-submit" type="submit">Create account <span>→</span></button>
    </form>
    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}" wire:navigate>Sign in</a></p>
</div>
