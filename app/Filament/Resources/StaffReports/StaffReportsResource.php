<?php

namespace App\Filament\Resources\StaffReports;

use App\Filament\Resources\StaffReports\Pages\CreateStaffReports;
use App\Filament\Resources\StaffReports\Pages\EditStaffReports;
use App\Filament\Resources\StaffReports\Pages\ListStaffReports;
use App\Filament\Resources\StaffReports\Pages\ViewStaffReports;
use App\Filament\Resources\StaffReports\Schemas\StaffReportsForm;
use App\Filament\Resources\StaffReports\Schemas\StaffReportsInfolist;
use App\Filament\Resources\StaffReports\Tables\StaffReportsTable;
use App\Models\StaffReports;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaffReportsResource extends Resource
{
    protected static ?string $model = StaffReports::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'StaffReports';

    public static function form(Schema $schema): Schema
    {
        return StaffReportsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffReportsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffReports::route('/'),
            'create' => CreateStaffReports::route('/create'),
            'view' => ViewStaffReports::route('/{record}'),
            'edit' => EditStaffReports::route('/{record}/edit'),
        ];
    }
}
