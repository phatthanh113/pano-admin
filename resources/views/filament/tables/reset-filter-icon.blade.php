@if(($filterIndicators ?? null) && count($filterIndicators) > 0)
    <div class="flex items-center gap-2 ml-auto">
        <button
            type="button"
            wire:click="resetTableFilters"
            wire:loading.attr="disabled"
            class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:text-gray-400 transition shrink-0"
            title="{{ __('filament-tables::table.filters.actions.reset.label') !== 'filament-tables::table.filters.actions.reset.label' ? __('filament-tables::table.filters.actions.reset.label') : 'Reset' }}"
        >
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
    </div>
@endif
