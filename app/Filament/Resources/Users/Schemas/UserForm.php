<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('ID / Username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Dùng để đăng nhập frontend (ID)'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Để trống khi edit nếu không đổi password')
                    ->dehydrateStateUsing(fn ($state) => filled($string = trim($state ?? '')) ? Hash::make($string) : null),
                Select::make('role')
                    ->label('Role')
                    ->options(['admin' => 'Admin (vào /admin)', 'user' => 'User (chỉ xem frontend)'])
                    ->default('user')
                    ->required()
                    ->helperText('Chỉ Admin mới vào được /admin'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
