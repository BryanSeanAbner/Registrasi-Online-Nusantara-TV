<?php

namespace App\Filament\Resources\FormField\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FormFieldForm
{
    public static function configure(Schema $schema): Schema
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
                ->rule('json')
                ->helperText('Contoh: {"rules":"min:3|max:50","options":["VIP","REGULAR"]}')
                ->afterStateHydrated(function ($component, $state) {
                    $component->state(
                        is_array($state) || is_object($state)
                            ? json_encode($state, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
                            : ($state ?? '')
                    );
                })
                ->dehydrateStateUsing(function ($state) {
                    return blank($state) ? null : json_decode($state, true);
                }),
        ]);
    }
}
