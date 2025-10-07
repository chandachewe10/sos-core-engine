<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;

class StaffInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2) // Set up 2-column layout
            ->components([
                TextEntry::make('phone'),
                TextEntry::make('full_name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('address'),
                TextEntry::make('hpcz_number'),
                IconEntry::make('is_approved')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),

                // Full-width section at the bottom
                Section::make('Attached Files')
                    ->columnSpanFull() // ✅ Makes it span full width
                    ->schema([
                        Actions::make([
                            Action::make('view_nrc')
                                ->label('View NRC')
                                ->icon('heroicon-o-eye')
                                ->url(fn ($record) => $record->getFirstMediaUrl('nrc'))
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->hasMedia('nrc'))
                                ->outlined()
                                ->color('secondary'),
                            
                            Action::make('download_nrc')
                                ->label('Download NRC')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn ($record) => $record->getFirstMediaUrl('nrc'))
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->hasMedia('nrc'))
                                ->outlined()
                                ->color('primary'),
                        ]),

                        Actions::make([
                            Action::make('view_selfie')
                                ->label('View Selfie')
                                ->icon('heroicon-o-eye')
                                ->url(fn ($record) => $record->getFirstMediaUrl('selfie'))
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->hasMedia('selfie'))
                                ->outlined()
                                ->color('secondary'),
                            
                            Action::make('download_selfie')
                                ->label('Download Selfie')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn ($record) => $record->getFirstMediaUrl('selfie'))
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->hasMedia('selfie'))
                                ->outlined()
                                ->color('primary'),
                        ]),
                    ])
                    ->collapsible(), // Optional: make it collapsible
            ]);
    }
}