<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-8 h-8 rounded-md object-contain border bg-white p-0.5 shrink-0">
                @else
                    <div class="w-8 h-8 rounded-md bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 shrink-0">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
                <div class="min-w-0">
                    <h3 class="font-semibold text-xs truncate">{{ $setting->company_name ?? config('app.name') }}</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                        {{ $setting->phone ?? '' }}{{ $setting->phone && $setting->email ? ' · ' : '' }}{{ $setting->email ?? '' }}
                    </p>
                </div>
            </div>
            <a href="{{ \App\Filament\Pages\SiteSettings::getUrl() }}"
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-500 text-white text-[11px] font-medium hover:bg-amber-600 transition shrink-0">
                <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                {{ __('app.settings') !== 'app.settings' ? __('app.settings') : 'Cài đặt' }}
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
