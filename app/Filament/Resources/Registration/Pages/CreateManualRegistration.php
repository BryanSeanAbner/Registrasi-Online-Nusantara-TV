<?php

namespace App\Filament\Resources\Registration\Pages;

use App\Filament\Resources\Registration\RegistrationResource;
use App\Models\Event;
use App\Repositories\Contracts\FormFieldRepositoryInterface;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Schema;
use BackedEnum;

class CreateManualRegistration extends Page
{
    protected static string $resource = RegistrationResource::class;

    protected static ?string $title = 'Daftarkan Peserta (Manual)';

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.pages.custome-page';

    public ?array $data = [
        'event_id' => null,
        'answers'  => [],
    ];

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make()->schema([
                Select::make('event_id')
                    ->label('Event')
                    ->options(fn () => Event::query()->orderBy('starts_at', 'desc')->pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                ...$this->dynamicFields(),
            ])
            ->statePath('data')
            ->footer(
                Schema::end([
                    Action::make('save')
                        ->label('Simpan Pendaftaran')
                        ->color('primary')
                        ->action('save'),

                    Action::make('save_and_create_another')
                        ->label('Simpan & Buat Lagi')
                        ->color('gray')
                        ->action('saveAndCreateAnother'),
                ])
            ),
        ]);
    }

    private function dynamicFields(): array
    {
        return [
            ...[SchemaForm::make()
                ->schema(fn (Get $get) => $this->buildEventComponents($get('event_id')))
                ->statePath('answers')
                ->columns(2)],
        ];
    }

    /**
     * @return array<\Filament\Forms\Components\Component>
     */
    private function buildEventComponents(?int $eventId): array
    {
        if (! $eventId) return [];

        /** @var FormFieldRepositoryInterface $repo */
        $repo = app(FormFieldRepositoryInterface::class);
        $event = Event::find($eventId);
        if (! $event) return [];

        $components = [];
        foreach ($repo->getFormFieldsForEvent($event) as $f) {
            $label = (string) $f->label;
            $name  = (string) $f->name;
            $req   = (bool) $f->is_required;
            $ph    = (string) ($f->placeholder ?? '');
            $help  = (string) ($f->help_text ?? '');
            $meta  = (array) ($f->meta ?? []);

            switch ($f->type) {
                case 'email':
                case 'text':
                case 'phone':
                case 'numeric':
                    $c = TextInput::make($name)
                        ->label($label)
                        ->placeholder($ph)
                        ->helperText($help)
                        ->required($req);
                    if ($f->type === 'email') $c->email();
                    if ($f->type === 'numeric') $c->numeric();
                    if ($f->type === 'phone') $c->tel();
                    if (!empty($meta['rules'])) $c->rule((string) $meta['rules']);
                    $components[] = $c;
                    break;

                case 'textarea':
                    $components[] = Textarea::make($name)
                        ->label($label)
                        ->placeholder($ph)
                        ->helperText($help)
                        ->rows(3)
                        ->required($req);
                    break;

                case 'select':
                    $options = [];
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $options = collect($meta['options'])->mapWithKeys(fn($v) => [(string)$v => (string)$v])->all();
                    }
                    $components[] = Select::make($name)
                        ->label($label)
                        ->options($options)
                        ->searchable()
                        ->required($req);
                    break;

                case 'radio':
                    $options = [];
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $options = collect($meta['options'])->mapWithKeys(fn($v) => [(string)$v => (string)$v])->all();
                    }
                    $components[] = Radio::make($name)
                        ->label($label)
                        ->options($options)
                        ->helperText($help)
                        ->required($req);
                    break;

                case 'checkbox':
                    $options = [];
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $options = collect($meta['options'])->mapWithKeys(fn($v) => [(string)$v => (string)$v])->all();
                    }
                    $components[] = CheckboxList::make($name)
                        ->label($label)
                        ->options($options)
                        ->helperText($help)
                        ->columns(1)
                        ->required($req);
                    break;

                case 'date':
                    $components[] = DatePicker::make($name)
                        ->label($label)
                        ->required($req);
                    break;

                case 'image':
                    $components[] = FileUpload::make($name)
                        ->label($label)
                        ->image()
                        ->directory('tmp/uploads')
                        ->disk('public')
                        ->required($req);
                    break;

                case 'toggle':
                    $components[] = Toggle::make($name)
                        ->label($label)
                        ->required($req);
                    break;

                default:
                    $components[] = TextInput::make($name)
                        ->label($label)
                        ->placeholder($ph)
                        ->helperText($help)
                        ->required($req);
                    break;
            }
        }

        return $components;
    }

    public function save(RegistrationService $service): void
    {
        $registration = $this->performSave($service);
        if (! $registration) return;

        redirect(RegistrationResource::getUrl('view', ['record' => $registration]));
    }

    public function saveAndCreateAnother(RegistrationService $service): void
    {
        $registration = $this->performSave($service);
        if (! $registration) return;

        $this->data['answers'] = [];

        Notification::make()
            ->title('Pendaftaran dibuat. Silakan tambah peserta lagi.')
            ->success()
            ->send();
    }

    /**
     * Shared save logic: validate & persist registration
     */
    private function performSave(RegistrationService $service)
    {
        $eventId = (int) ($this->data['event_id'] ?? 0);
        $event = Event::find($eventId);
        if (! $event) {
            Notification::make()->title('Pilih event terlebih dahulu')->danger()->send();
            return null;
        }

        $rules = collect($service->buildValidationRules($event))
            ->mapWithKeys(fn ($rule, $key) => ["data.answers.$key" => $rule])
            ->all();

        if (! empty($rules)) {
            $this->validate($rules);
        }

        $answers = (array) ($this->data['answers'] ?? []);

        $registration = $service->createRegistration($event, $answers);

        Notification::make()
            ->title('Pendaftaran dibuat')
            ->success()
            ->send();

        return $registration;
    }
}
