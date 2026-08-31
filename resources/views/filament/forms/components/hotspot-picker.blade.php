<div
    x-data="{
        yaw: $wire.entangle('data.yaw'),
        pitch: $wire.entangle('data.pitch'),
        hasPanorama: @json($panorama !== null),
        onImageClick(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            const w = rect.width;
            const h = rect.height;
            let newYaw = ((x / w) * 360 - 180);
            let newPitch = (90 - (y / h) * 180);
            newYaw = Math.round(newYaw * 10) / 10;
            newPitch = Math.round(newPitch * 10) / 10;
            newPitch = Math.max(-90, Math.min(90, newPitch));
            this.yaw = newYaw;
            this.pitch = newPitch;
            $wire.set('data.yaw', newYaw);
            $wire.set('data.pitch', newPitch);
        }
    }"
    class="space-y-2"
>
    <div class="text-sm text-gray-600 dark:text-gray-300">
        @if($panorama)
            <span class="font-medium">Panorama: {{ $panorama->name }}</span> ({{ $panorama->slug }}) —
            <span>{{ __('forms.click_on_image') }}</span>
        @else
            <span class="text-amber-600">{{ __('forms.choose_panorama_first') }}</span>
        @endif
    </div>

    <div class="relative border rounded-lg overflow-hidden bg-gray-50" style="padding:12px;text-align:center;">
        <div style="position:relative;display:inline-block;max-width:650px;width:100%;cursor:crosshair;vertical-align:top;" x-on:click="onImageClick($event)">
            <img
                src="{{ $displayUrl }}"
                alt="Panorama preview"
                class="block select-none"
                draggable="false"
                style="display:block;width:100%;height:auto;max-width:100%;max-height:400px;object-fit:contain;"
            />
            @if($panorama)
            <div x-show="hasPanorama && yaw !== null && yaw !== '' && pitch !== null && pitch !== ''"
                 x-bind:style="'position:absolute;left:'+((parseFloat(yaw)+180)/360*100)+'%;top:'+((90-parseFloat(pitch))/180*100)+'%;width:26px;height:26px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.5);pointer-events:none;'"
                 style="display:none;"
                 title="Hotspot"></div>
            @endif
            <div class="absolute inset-0 pointer-events-none opacity-10" style="background-image: linear-gradient(to right,white 1px,transparent 1px),linear-gradient(to bottom,white 1px,transparent 1px);background-size:10% 10%;"></div>
        </div>
    </div>

    <div class="flex gap-4 text-xs text-gray-500">
        <span>Yaw: <strong x-text="yaw ?? '—'"></strong>° (-180 → 180)</span>
        <span>Pitch: <strong x-text="pitch ?? '—'"></strong>° (-90 → 90)</span>
        <span class="ml-auto">{{ __('forms.click_on_image') }}</span>
    </div>

    <div class="text-xs bg-blue-50 border border-blue-200 rounded p-2 dark:bg-blue-900/20">
        <strong>{{ __('forms.how_to') }}</strong>
    </div>
</div>
