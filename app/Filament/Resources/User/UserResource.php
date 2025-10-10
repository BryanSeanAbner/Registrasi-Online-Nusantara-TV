<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\Pages;
use App\Filament\Resources\User\Schemas\UserForm;
use App\Filament\Resources\User\Tables\UsersTable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && (Auth::user()?->role === 'super_admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }
}
