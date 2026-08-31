@props(['size' => 'sm'])
@php $current = app()->getLocale(); @endphp
<div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <select onchange="window.location.href='{{ url('/lang') }}/'+this.value"
            aria-label="{{ __('app.lang') }}"
            class="h-7 pl-2 pr-6 rounded-md text-xs font-medium border bg-white text-gray-700 border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 cursor-pointer leading-none">
        <option value="en" @selected($current==='en')>EN</option>
        <option value="vi" @selected($current==='vi')>VI</option>
        <option value="ja" @selected($current==='ja')>JA</option>
    </select>
</div>
