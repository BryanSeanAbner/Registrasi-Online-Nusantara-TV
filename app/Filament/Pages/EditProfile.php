<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use UnitEnum;

class EditProfile extends Page
{
    protected static string|BackedEnum|null  $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Edit Profile';
    protected static string|UnitEnum|null $navigationGroup = 'Account';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.custome-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function mount(): void
    {
        $user = Auth::user();

        $this->data = [
            'name' => (string) $user?->name,
            'email' => (string) $user?->email,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make()->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->rule(fn () => Rule::unique('users', 'email')->ignore(Auth::id())),

                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->rule('confirmed')
                    ->maxLength(255),

                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->revealable()
                    ->dehydrated(false),
            ])
            ->statePath('data')
            ->footer([
                Action::make('save')
                    ->label('Save Changes')
                    ->color('primary')
                    ->action('save'),
            ]),
        ]);
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $data = $this->data ?? [];

        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        // Clear sensitive fields
        $this->data['password'] = null;
        $this->data['password_confirmation'] = null;

        Notification::make()
            ->success()
            ->title('Profile updated')
            ->send();
    }
}

