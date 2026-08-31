<?php

namespace App\Filament\Resources\Hotspots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HotspotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('panorama.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('targetPanorama.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tooltip')
                    ->searchable(),
                TextColumn::make('yaw')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pitch')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
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
                SelectFilter::make('panorama_id')
                    ->label(fn () => __('app.panorama_source'))
                    ->relationship('panorama', 'name')
                    ->searchable()->preload(),
                SelectFilter::make('target_panorama_id')
                    ->label(fn () => __('app.panorama_target'))
                    ->relationship('targetPanorama', 'name')
                    ->searchable()->preload(),
            ])
            ->persistFiltersInSession()
            ->deselectAllRecordsWhenFiltered(false)
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->successNotificationTitle('Đã clone')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['tooltip'] = ($data['tooltip'] ?? '').' (Copy)';
                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
