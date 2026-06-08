<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static string $view = 'filament.auth.login';

    protected ?string $maxWidth = 'md';

    public function getTitle(): string | Htmlable
    {
        return 'Masuk ke SiKaMo';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Masuk ke SiKaMo';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Lanjutkan kasir, inventaris, dan laporan toko dari satu dashboard.';
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->placeholder('nama@toko.com')
            ->prefixIcon('heroicon-m-envelope')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->placeholder('Masukkan password')
            ->prefixIcon('heroicon-m-lock-closed')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Ingat sesi login saya');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Masuk ke Dashboard');
    }
}
