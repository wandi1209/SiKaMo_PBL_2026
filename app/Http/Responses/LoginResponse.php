<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();
        \Illuminate\Support\Facades\Log::info("LoginResponse fired for user " . $user->email . " with role " . $user->role);

        // Redirect berdasarkan role
        if ($user->role === 'admin') {
            return redirect()->to('/admin');
        }

        if (in_array($user->role, ['owner', 'cashier'])) {
            return redirect()->to('/merchant');
        }

        // Fallback default
        return redirect()->to('/');
    }
}
