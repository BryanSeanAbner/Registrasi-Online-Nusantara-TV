<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Checkbox;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')
                ->label('Email atau Username')
                ->required()
                ->autofocus()
                ->autocomplete('username'),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required()
                ->extraAttributes(['class' => 'mb-4']),

            Checkbox::make('remember')
                ->label(__('filament-panels::auth/pages/login.form.remember.label')),
        ]);
    }

    /**
     * Transform form data into auth credentials, allowing email or username.
     *
     * @param  array<string,mixed>  $data
     * @return array<string,string>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $value = (string) ($data['email'] ?? '');
        $password = (string) ($data['password'] ?? '');

        $field = filter_var($value, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $value,
            'password' => $password,
        ];
    }
}
