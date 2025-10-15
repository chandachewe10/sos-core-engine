<?php

namespace App\Filament\Resources\StaffReports\Pages;

use App\Filament\Resources\StaffReports\StaffReportsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffReports extends EditRecord
{
    protected static string $resource = StaffReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
