<?php

namespace App\Filament\Resources\StaffReports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StaffReportsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('staff_id')
                    ->required()
                    ->numeric(),
                TextInput::make('case_id')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('severity')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'])
                    ->default('medium')
                    ->required(),
                Textarea::make('outcome')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
