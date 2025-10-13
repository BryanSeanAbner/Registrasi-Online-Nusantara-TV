<?php

namespace App\Filament\Resources\FormField;

use App\Filament\Resources\FormField\Pages;
use App\Filament\Resources\FormField\Schemas\FormFieldForm;
use App\Filament\Resources\FormField\Tables\FormFieldsTable;
use App\Models\FormField;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FormFieldResource extends Resource
{
    protected static ?string $model = FormField::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Event Management';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Schema $schema): Schema
    {
        return FormFieldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormFieldsTable::configure($table);
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
