<?php

namespace App\Filament\Resources\Panoramas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PanoramasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('building.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('floor.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('number')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->visibility('public')
                    ->checkFileExistence(false)
                    ->imageHeight(48)
                    ->square()
                    ->getStateUsing(function ($record) {
                        $v = $record->thumbnail;
                        if (blank($v)) return null;
                        if (str_starts_with($v, 'http')) return $v;
                        if (str_starts_with($v, '/')) {
                            $path = ltrim($v, '/');
                            if (Storage::disk('public')->exists($path) || Storage::disk('public')->exists($v) || file_exists(public_path($v)) || file_exists(public_path($path))) {
                                return url($v);
                            }
                            return null;
                        }
                        if (Storage::disk('public')->exists($v)) return Storage::disk('public')->url($v);
                        if (file_exists(public_path($v)) || file_exists(public_path('/'.$v))) return url('/'.$v);
                        return null;
                    }),
                ImageColumn::make('url')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->checkFileExistence(false)
                    ->imageHeight(48)
                    ->getStateUsing(function ($record) {
                        $v = $record->url;
                        if (blank($v)) return null;
                        if (str_starts_with($v, 'http')) return $v;
                        if (str_starts_with($v, '/')) {
                            $path = ltrim($v, '/');
                            if (Storage::disk('public')->exists($path) || Storage::disk('public')->exists($v) || file_exists(public_path($v)) || file_exists(public_path($path))) {
                                return url($v);
                            }
                            return null;
                        }
                        if (Storage::disk('public')->exists($v)) return Storage::disk('public')->url($v);
                        if (file_exists(public_path($v)) || file_exists(public_path('/'.$v))) return url('/'.$v);
                        return null;
                    }),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('map_x')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('map_y')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('map_angle')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('yaw')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pitch')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
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
                SelectFilter::make('project_id')
                    ->label(fn () => __('app.project'))
                    ->relationship('project', 'name')
                    ->searchable()->preload(),
                SelectFilter::make('building_id')
                    ->label(fn () => __('app.building'))
                    ->relationship('building', 'name')
                    ->searchable()->preload(),
                SelectFilter::make('floor_id')
                    ->label(fn () => __('app.floor'))
                    ->relationship('floor', 'name')
                    ->searchable()->preload(),
                TernaryFilter::make('is_active')
                    ->label(fn () => __('app.active')),
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
                        $data['slug'] = ($data['slug'] ?? 'pano').'-copy-'.time();
                        $data['name'] = ($data['name'] ?? '').' (Copy)';
                        return $data;
                    })
                    ->beforeReplicaSaved(function ($record, $replica) {
                        foreach (['thumbnail', 'url'] as $field) {
                            $path = $record->{$field};
                            if (blank($path) || str_starts_with($path, 'http') || str_starts_with($path, '/')) continue;
                            $clean = ltrim($path, '/');
                            if (! Storage::disk('public')->exists($clean)) continue;
                            $ext = pathinfo($clean, PATHINFO_EXTENSION) ?: 'jpg';
                            $newPath = dirname($clean).'/'.pathinfo($clean, PATHINFO_FILENAME).'-copy-'.time().'.'.$ext;
                            if (dirname($clean) === '.') $newPath = 'panoramas/'.basename($newPath);
                            Storage::disk('public')->copy($clean, $newPath);
                            $replica->{$field} = $newPath;
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
