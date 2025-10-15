<?php

namespace App\Filament\Resources\StaffReports\Pages;

use App\Filament\Resources\StaffReports\StaffReportsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffReports extends ViewRecord
{
    protected static string $resource = StaffReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
