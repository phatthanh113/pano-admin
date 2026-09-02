<div
    x-data="{
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
            // Tìm repeater item chứa ảnh này và set input yaw/pitch
            const item = event.currentTarget.closest('[data-repeater-item]') || event.currentTarget.closest('.fi-fo-repeater-item') || event.currentTarget.parentElement.closest('li') || document;
            // Thử nhiều selector cho Filament v3
            let yawInput = item.querySelector('input[wire\\:model*=\".yaw\"]') || item.querySelector('input[id*=\"yaw\"]');
            let pitchInput = item.querySelector('input[wire\\:model*=\".pitch\"]') || item.querySelector('input[id*=\"pitch\"]');
            // Fallback: tìm trong toàn bộ repeater nếu không thấy
            if (!yawInput) {
                const allYaws = document.querySelectorAll('input[wire\\:model*=\".yaw\"]');
                // Lấy input gần nhất với ảnh click (heuristic: cùng repeater item index)
                yawInput = event.currentTarget.closest('.fi-fo-repeater-item-content')?.querySelector('input[wire\\:model*=\".yaw\"]') || allYaws[0];
            }
            if (!pitchInput) {
                pitchInput = event.currentTarget.closest('.fi-fo-repeater-item-content')?.querySelector('input[wire\\:model*=\".pitch\"]');
            }
            if (yawInput) {
                yawInput.value = newYaw;
                yawInput.dispatchEvent(new Event('input', { bubbles: true }));
                yawInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (pitchInput) {
                pitchInput.value = newPitch;
                pitchInput.dispatchEvent(new Event('input', { bubbles: true }));
                pitchInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }"
    class="space-y-2"
>
    @if($panoramaUrl)
        <div class="text-xs text-gray-500 mb-1">{{ __('forms.click_on_image') }} — Yaw/Pitch sẽ tự điền</div>
        <div class="relative border rounded-lg overflow-hidden bg-gray-50" style="padding:8px;text-align:center;">
            <div style="position:relative;display:inline-block;max-width:100%;width:100%;cursor:crosshair;" x-on:click="onImageClick($event)">
                <img
                    src="{{ $panoramaUrl }}"
                    alt="Panorama preview"
                    class="block select-none"
                    draggable="false"
                    style="display:block;width:100%;height:auto;max-width:100%;max-height:280px;object-fit:contain;"
                />
                <div class="absolute inset-0 pointer-events-none opacity-10" style="background-image: linear-gradient(to right,white 1px,transparent 1px),linear-gradient(to bottom,white 1px,transparent 1px);background-size:10% 10%;"></div>
            </div>
        </div>
    @else
        <div class="text-xs text-amber-600 border border-amber-200 bg-amber-50 rounded p-2">
            Hãy upload ảnh Panorama ở trên trước, sau đó ảnh sẽ hiện ở đây để bạn click chọn vị trí hotspot.
        </div>
    @endif
</div>
