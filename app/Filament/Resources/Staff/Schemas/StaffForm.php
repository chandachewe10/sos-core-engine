<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\FileUpload;
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
                    ,
                TextInput::make('hpcz_number')
                    ->default(null),
                FileUpload::make('nrc_uri')
                    ->label('NRC Document'),
                FileUpload::make('selfie_uri')
                    ->label('Profile Picture'),
                FileUpload::make('signature_uri')
                    ->label('Signature Image'),
               
                Toggle::make('is_approved')
                    ->required(),
            ]);
    }
}
