<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 99;

    protected static ?string $title = null;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('app.settings') !== 'app.settings' ? __('app.settings') : 'Cài đặt chung';
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public function mount(): void
    {
        $setting = SiteSetting::current();
        $this->form->fill($setting->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('app.company_info') !== 'app.company_info' ? __('app.company_info') : 'Thông tin công ty')
                    ->columns(2)
                    ->components([
                        TextInput::make('company_name')
                            ->label('Tên công ty')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        FileUpload::make('logo')
                            ->label('Logo công ty')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('site')
                            ->maxSize(2048)
                            ->imagePreviewHeight('120')
                            ->columnSpan(1),
                        \Filament\Schemas\Components\Grid::make(1)
                            ->columnSpan(1)
                            ->components([
                                TextInput::make('phone')
                                    ->label('Số điện thoại')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('website')
                                    ->label('Website')
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('facebook')
                                    ->label('Facebook')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        TextInput::make('address')
                            ->label('Địa chỉ')
                            ->columnSpanFull()
                            ->maxLength(500),
                        Textarea::make('description')
                            ->label('Mô tả / Giới thiệu')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('copyright')
                            ->label('Copyright')
                            ->placeholder('© 2026 Pano Company')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = SiteSetting::current();
        $setting->update($data);
        Notification::make()
            ->title('Đã lưu cài đặt')
            ->success()
            ->send();
    }
}
