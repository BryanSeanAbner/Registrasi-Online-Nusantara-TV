<?php

namespace App\Filament\Pages;

use App\Models\Event;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class ParticipantScan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;
    protected static string|UnitEnum|null $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Participant Scan';
    protected static ?string $title = 'Participant Scan';
    protected static ?int $navigationSort = 999; // put at bottom

    protected string $view = 'filament.pages.table-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('is_published', true)
                    ->withCount([
                        'registrations as participants_count' => function ($q) {
                            $q->whereNotNull('checked_in_at');
                        },
                    ])
            )
            ->columns([
                TextColumn::make('title')->label('Event')->searchable()->sortable(),
                TextColumn::make('participants_count')->label('Total Participant Scan')->sortable(),
                TextColumn::make('starts_at')->label('Starts')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')->label('Slug')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(null)
            ->recordActions([
                Action::make('view')
                    ->label('View Detail')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Event $e) => route('event.participants', $e->slug))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
