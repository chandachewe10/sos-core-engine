<?php

namespace App\Filament\Resources\StaffReports\Pages;

use App\Filament\Resources\StaffReports\StaffReportsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffReports extends ListRecords
{
    protected static string $resource = StaffReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
