<?php

namespace App\Filament\Resources\Buildings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BuildingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(['single' => 'Single (panorama trực tiếp)', 'group' => 'Group (chứa nhiều tầng)'])
                    ->required()
                    ->default('single'),
                FileUpload::make('plan_image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('buildings')
                    ->maxSize(5120)
                    ->imagePreviewHeight('150'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
