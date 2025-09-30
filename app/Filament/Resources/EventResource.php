<?php

namespace App\Filament\Resources;
use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class EventResource extends Resource {
    protected static ?string $model = Event::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Flex::make([
                Section::make([
                    TextInput::make('title')->label('Title')->required(),
                    TextInput::make('slug')->label('Slug')->required(),
                    TextInput::make('venue')->label('Venue')->required(),
                ]),
                Section::make([
                    DateTimePicker::make('starts_at')
                        ->label('Starts At')
                        ->required(),
                    DateTimePicker::make('ends_at')
                        ->label('Ends At')
                        ->required(),
                    Toggle::make('is_published'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('venue'),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('ends_at')->dateTime()->sortable(),
                IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->recordUrl(null)
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->url(fn (Event $e) => route('event.show', $e->slug))->openUrlInNewTab(),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->label('Edit')
                    ->url(fn (Event $e) => static::getUrl('edit', ['record' => $e])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}