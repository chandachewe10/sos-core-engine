<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ->password()
                    ->required(),
                TextInput::make('hpcz_number')
                    ->default(null),
                TextInput::make('nrc_uri')
                    ->default(null),
                TextInput::make('selfie_uri')
                    ->default(null),
                TextInput::make('signature_uri')
                    ->default(null),
                Toggle::make('is_approved')
                    ->required(),
            ]);
    }
}
