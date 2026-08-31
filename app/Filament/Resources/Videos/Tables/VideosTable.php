<?php

namespace App\Filament\Resources\Videos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('videoable_type')
                    ->searchable(),
                TextColumn::make('videoable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
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
                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path) || \Illuminate\Support\Facades\Storage::disk('public')->exists($v) || file_exists(public_path($v)) || file_exists(public_path($path))) {
                                return url($v);
                            }
                            return null;
                        }
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($v)) return \Illuminate\Support\Facades\Storage::disk('public')->url($v);
                        if (file_exists(public_path($v)) || file_exists(public_path('/'.$v))) return url('/'.$v);
                        return null;
                    }),
                TextColumn::make('video_url')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->successNotificationTitle('Đã clone')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['title'] = ($data['title'] ?? '').' (Copy)';
                        return $data;
                    })
                    ->beforeReplicaSaved(function ($record, $replica) {
                        foreach (['thumbnail'] as $field) {
                            $path = $record->{$field};
                            if (blank($path) || str_starts_with($path, 'http') || str_starts_with($path, '/')) continue;
                            $clean = ltrim($path, '/');
                            if (! Storage::disk('public')->exists($clean)) continue;
                            $ext = pathinfo($clean, PATHINFO_EXTENSION) ?: 'jpg';
                            $newPath = dirname($clean).'/'.pathinfo($clean, PATHINFO_FILENAME).'-copy-'.time().'.'.$ext;
                            if (dirname($clean) === '.') $newPath = 'videos/'.basename($newPath);
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
