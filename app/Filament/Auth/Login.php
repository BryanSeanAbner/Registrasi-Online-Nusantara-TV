<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make()->schema([
                TextInput::make('login')
                    ->label('Email atau Username')
                    ->required()
                    ->autofocus()
                    ->autocomplete('username'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(),
            ]),
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
        $login = (string) ($data['login'] ?? '');
        $password = (string) ($data['password'] ?? '');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $login,
            'password' => $password,
        ];
    }
}
