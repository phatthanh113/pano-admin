<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use SensitiveParameter;

class CustomLogin extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email hoặc ID')
            ->placeholder('Nhập email hoặc ID (ví dụ: admin)')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        // Nếu nhập dạng email thì giữ nguyên
        if (str_contains($login, '@')) {
            return [
                'email' => $login,
                'password' => $password,
            ];
        }

        // Nếu nhập ID / name thì tìm user theo name để lấy email thật
        $user = User::where('name', $login)->first();

        if ($user) {
            return [
                'email' => $user->email,
                'password' => $password,
            ];
        }

        // Không tìm thấy -> vẫn trả về login như email để báo lỗi failed
        return [
            'email' => $login,
            'password' => $password,
        ];
    }
}
