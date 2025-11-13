<?php

namespace App\Filament\Resources\User\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('username')
                ->label('Username')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->maxLength(255)
                ->rule('alpha_dash')
                ->unique(ignoreRecord: true),

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->maxLength(255),

            Select::make('role')
                ->label('Role')
                ->options(function (string $operation) {
                    return [
                        'admin' => 'Admin',
                    ];
                })
                ->default('admin')
                ->required()
                ->disabled()
                ->dehydrated(false)
                ->rule('in:admin')
                ->helperText('Role dikunci ke Admin. Super Admin hanya bisa di-set di luar UI.'),
        ]);
    }
}
