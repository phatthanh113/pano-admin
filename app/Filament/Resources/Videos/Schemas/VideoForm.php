<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                MorphToSelect::make('videoable')
                    ->types([
                        \Filament\Forms\Components\MorphToSelect\Type::make(\App\Models\Building::class)->titleAttribute('name'),
                        \Filament\Forms\Components\MorphToSelect\Type::make(\App\Models\Floor::class)->titleAttribute('name'),
                    ])
                    ->label('Gắn với Building/Floor'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('label'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('thumbnail')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('videos')
                    ->maxSize(5120)
                    ->imagePreviewHeight('150'),
                TextInput::make('video_url')
                    ->label('YouTube/Video URL')
                    ->url()
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
