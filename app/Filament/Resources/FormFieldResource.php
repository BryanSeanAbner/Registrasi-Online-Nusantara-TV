<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormFieldResource\Pages;
use App\Models\FormField;
use Filament\Actions\Action;
use Filament\Forms\Components\{Select, Textarea, TextInput, Toggle};
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{IconColumn, TextColumn};

class FormFieldResource extends Resource
{
    protected static ?string $model = FormField::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Event Management';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('event_id')
                ->relationship('event', 'title')
                ->required()
                ->label('Event'),

            TextInput::make('label')
                ->required()
                ->label('Label'),

            TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Key unik, misal: email, phone_number'),

            Select::make('type')
                ->options([
                    'text' => 'Text',
                    'email' => 'Email',
                    'numeric' => 'Numeric',
                    'image' => 'Image Upload',
                    'date' => 'Date',
                    'datetime' => 'DateTime',
                    'textarea' => 'Textarea',
                    'select' => 'Select',
                    'radio' => 'Radio',
                    'checkbox' => 'Checkbox',
                    'phone' => 'Phone',
                    'url' => 'URL',
                ])
                ->required(),

            Toggle::make('is_required')->label('Required'),
            Toggle::make('is_toggleable')->label('Toggleable'),
            Toggle::make('is_hidden_by_default')->label('Hidden by default'),
            Toggle::make('show_in_form')->label('Show in Form'),
            Toggle::make('show_in_scan')->label('Show in Scan'),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->label('Order'),

            Textarea::make('meta')
                ->rows(3)
                ->label('Meta JSON')
                ->helperText('Contoh: {"rules":"min:3|max:50","options":["VIP","REGULAR"]}'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('event.title')->label('Event'),
            TextColumn::make('label')->searchable(),
            TextColumn::make('name'),
            TextColumn::make('type'),
            IconColumn::make('is_required')->boolean(),
            TextColumn::make('sort_order')->sortable(),
            TextColumn::make('updated_at')->dateTime(),
        ])
        ->recordActions([
            Action::make('edit')
                ->icon('heroicon-m-pencil-square')
                ->label('Edit')
                ->url(fn (FormField $e) => static::getUrl('edit', ['record' => $e])),
            Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (FormField $r) => $r->delete()),
        ])          
        ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFormFields::route('/'),
            'create' => Pages\CreateFormField::route('/create'),
            'edit'   => Pages\EditFormField::route('/{record}/edit'),
        ];
    }
}
