<?php

namespace App\Filament\Resources\StaffReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StaffReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('staff.full_name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('case_id')
                    ->label('Case ID')
                    ->badge()
                    ->searchable(),
                TextColumn::make('severity')
                    ->badge()
                    ->colors([
                        'primary' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'critical',
                    ])
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('outcome')
                    ->limit(50)
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
