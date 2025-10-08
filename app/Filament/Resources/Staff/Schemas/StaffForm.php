<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('full_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('password')
                    ->password(),
                TextInput::make('hpcz_number')
                    ->default(null),


                Select::make('is_approved')
                    ->label('Approval Status')
                    ->required()
                    ->options([
                        2 => 'Pending',
                        1 => 'Approved',
                        3 => 'Rejected',
                    ])

            ]);
    }
}
