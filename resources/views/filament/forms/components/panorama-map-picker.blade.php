<div
    x-data="{
        map_x: $wire.entangle('data.map_x'),
        map_y: $wire.entangle('data.map_y'),
        map_angle: $wire.entangle('data.map_angle'),
        onPlanClick(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            const w = rect.width;
            const h = rect.height;
            let newX = Math.round((x / w * 100) * 10) / 10;
            let newY = Math.round((y / h * 100) * 10) / 10;
            newX = Math.max(0, Math.min(100, newX));
            newY = Math.max(0, Math.min(100, newY));
            this.map_x = newX;
            this.map_y = newY;
            $wire.set('data.map_x', newX);
            $wire.set('data.map_y', newY);
        }
    }"
    class="space-y-2"
>
    @php
        // $planUrl, $planLabel được truyền từ PanoramaForm viewData
    @endphp

    <div class="text-sm text-gray-600 dark:text-gray-300">
        @if($planUrl)
            <span class="font-medium">Plan: {{ $planLabel }}</span> —
            <span>{{ __('forms.click_on_map') }}</span>
        @else
            <span class="text-amber-600">{{ __('forms.select_building_floor_first') }}</span>
        @endif
    </div>

    <div class="relative border rounded-lg overflow-hidden bg-gray-50" style="padding:12px;text-align:center;">
        @if($planUrl)
            <div style="position:relative;display:inline-block;max-width:650px;width:100%;cursor:crosshair;vertical-align:top;" x-on:click="onPlanClick($event)">
                <img
                    src="{{ $planUrl }}"
                    alt="Plan preview"
                    class="block select-none"
                    draggable="false"
                    style="display:block;width:100%;height:auto;max-width:100%;"
                />
                <div x-show="map_x !== null && map_x !== '' && map_y !== null && map_y !== ''"
                     x-bind:style="'position:absolute;left:'+(parseFloat(map_x)||0)+'%;top:'+(parseFloat(map_y)||0)+'%;width:26px;height:26px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.5);pointer-events:none;display:flex;align-items:center;justify-content:center;'"
                     style="display:none;"
                >
                    <span style="color:#fff;font-size:9px;font-weight:bold;line-height:1;" x-text="$wire.get('data.number') || '•'"></span>
                </div>
                <div x-show="map_x !== null && map_x !== '' && map_y !== null && map_y !== ''"
                     x-bind:style="'position:absolute;left:'+(parseFloat(map_x)||0)+'%;top:'+(parseFloat(map_y)||0)+'%;width:26px;height:2px;background:#dc2626;transform:translateY(-50%) rotate('+(parseFloat(map_angle)||0)+'deg);transform-origin:left center;z-index:9;pointer-events:none;'"
                     style="display:none;"
                >
                    <div style="position:absolute;right:0;top:50%;width:0;height:0;transform:translateY(-50%);border-left:6px solid #dc2626;border-top:4px solid transparent;border-bottom:4px solid transparent;"></div>
                </div>
            </div>
        @else
            <div class="w-full flex flex-col items-center justify-center text-gray-400 border border-dashed rounded-lg bg-white" style="height:120px;max-width:650px;margin:0 auto;">
                <span class="text-sm">Chưa có bản đồ để hiển thị</span>
                <span class="text-xs mt-1">Chọn Building / Floor để hiện bản đồ</span>
            </div>
        @endif
    </div>

    <div class="flex gap-4 text-xs text-gray-500 items-center mt-2 px-1">
        <span>X: <strong x-text="map_x ?? '—'"></strong>%</span>
        <span>Y: <strong x-text="map_y ?? '—'"></strong>%</span>
        <span>Angle: <strong x-text="map_angle ?? '0'"></strong>°</span>
        <span class="ml-auto hidden sm:inline">{{ __('forms.click_on_image') }}</span>
    </div>
</div>
