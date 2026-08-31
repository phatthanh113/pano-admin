<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                ImageColumn::make('thumbnail'),
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
                        $data['slug'] = ($data['slug'] ?? 'project').'-copy-'.time();
                        $data['name'] = ($data['name'] ?? '').' (Copy)';
                        return $data;
                    })
                    ->beforeReplicaSaved(function ($record, $replica) {
                        foreach (['thumbnail'] as $field) {
                            $path = $record->{$field};
                            if (blank($path) || str_starts_with($path, 'http')) continue;
                            $clean = ltrim($path, '/');
                            if (! Storage::disk('public')->exists($clean)) continue;
                            $ext = pathinfo($clean, PATHINFO_EXTENSION) ?: 'jpg';
                            $newPath = dirname($clean).'/'.pathinfo($clean, PATHINFO_FILENAME).'-copy-'.time().'.'.$ext;
                            if (dirname($clean) === '.') $newPath = 'projects/'.basename($newPath);
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
