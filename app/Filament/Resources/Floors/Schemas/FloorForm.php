<?php

namespace App\Filament\Resources\Floors\Schemas;

use App\Models\Building;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FloorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Dự án')
                    ->relationship('project', 'name')
                    ->searchable()->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('building_id', null))
                    ->afterStateHydrated(function ($state, callable $set, Get $get) {
                        if (empty($state) && ! empty($get('building_id'))) {
                            $b = Building::find($get('building_id'));
                            if ($b && $b->project_id) $set('project_id', $b->project_id);
                        }
                    })
                    ->dehydrated(true),
                Select::make('building_id')
                    ->label('Building')
                    ->searchable()->preload()
                    ->live()
                    ->placeholder('— Default Building —')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! empty($state)) {
                            $b = Building::find($state);
                            if ($b && $b->project_id) $set('project_id', $b->project_id);
                        }
                    })
                    ->options(function (Get $get) {
                        $projectId = $get('project_id');
                        // Khi edit, nếu project_id trống mà floor đã có building thì lấy project từ building đó để filter
                        if (empty($projectId)) {
                            $buildingId = $get('building_id');
                            if (! empty($buildingId)) {
                                $b = Building::find($buildingId);
                                if ($b) $projectId = $b->project_id;
                            }
                        }
                        $query = Building::query()->orderBy('sort_order');
                        if (! empty($projectId)) $query->where('project_id', $projectId);
                        return $query->pluck('name', 'id');
                    }),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('short_label'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('plan_image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('floor-plans')
                    ->maxSize(5120)
                    ->imagePreviewHeight('150')
                    ->panelAspectRatio('16:9'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
