<?php

namespace App\Filament\Resources\FormField\Schemas;

use App\Models\Event;
use App\Models\FormField as FormFieldModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FormFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('event_id')
                ->relationship('event', 'title')
                ->required()
                ->reactive()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $label = $get('label');
                    $eventSlug = null;
                    if ($state) {
                        $event = Event::find($state);
                        $eventSlug = $event?->slug ?: $event?->title;
                    }
                    $labelSlug = Str::slug($label ?? '', '_');
                    $eventPart = $eventSlug ? Str::slug($eventSlug, '_') : '';
                    $name = trim($labelSlug . ($eventPart ? '_' . $eventPart : ''), '_');
                    $set('name', $name);

                    // Auto-suggest next order for this event
                    if ($state) {
                        $max = (int) (FormFieldModel::where('event_id', $state)->max('sort_order') ?? -1);
                        $set('sort_order', $max + 1);
                    }
                })
                ->label('Event'),

            TextInput::make('label')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $eventId = $get('event_id');
                    $eventSlug = null;
                    if ($eventId) {
                        $event = Event::find($eventId);
                        $eventSlug = $event?->slug ?: $event?->title;
                    }
                    $labelSlug = Str::slug($state ?? '', '_');
                    $eventPart = $eventSlug ? Str::slug($eventSlug, '_') : '';
                    $name = trim($labelSlug . ($eventPart ? '_' . $eventPart : ''), '_');
                    $set('name', $name);
                })
                ->label('Label'),

            TextInput::make('name')
                ->disabled()
                ->dehydrated()
                ->required()
                ->unique(ignoreRecord: true)
                ->default(function (Get $get) {
                    $label = $get('label');
                    $eventId = $get('event_id');
                    $event = $eventId ? Event::find($eventId) : null;
                    $labelSlug = Str::slug($label ?? '', '_');
                    $eventSlug = $event ? Str::slug($event->slug ?: $event->title, '_') : '';
                    return trim($labelSlug . ($eventSlug ? '_' . $eventSlug : ''), '_');
                })
                ->helperText('Otomatis dari label + event, contoh: email_demo_day'),

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
                ->required()
                ->reactive(),

            Grid::make(2)->schema([
                Toggle::make('is_required')->label('Required'),
                Toggle::make('show_in_participant')->label('Show in Participant'),
                Toggle::make('show_in_scan')->label('Show in Scan'),
            ])->columnSpanFull(),

            

            TextInput::make('sort_order')
                ->label('Urutan Tampilan')
                ->numeric()
                ->minValue(1)
                ->step(1)
                ->placeholder('1 = paling atas')
                ->helperText('Semakin kecil angkanya, semakin atas tampilnya. Contoh: 1 tampil di atas 2.')
                ->default(function (Get $get) {
                    $eventId = $get('event_id');
                    if ($eventId) {
                        $max = (int) (FormFieldModel::where('event_id', $eventId)->max('sort_order') ?? -1);
                        return $max + 1;
                    }
                    return 1;
                }),

            TagsInput::make('meta.options')
                ->label('Options untuk Select/Radio/Checkbox')
                ->placeholder('Ketik satu opsi lalu tekan Enter (contoh: VIP, REGULAR)')
                ->suggestions([])
                ->helperText('Atur pilihan pengguna: ketik opsi lalu Enter. Untuk menghapus, klik tanda × di setiap opsi. Aktif saat tipe Select/Radio/Checkbox.')
                ->visible(fn (Get $get) => in_array(($get('type') ?? ''), ['select', 'radio', 'checkbox'])),
        ]);
    }
}
