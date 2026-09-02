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
            // Tìm repeater item đang mở (không collapsed) hoặc item cuối cùng
            let targetItem = document.querySelector('.fi-fo-repeater-item:not(.fi-collapsed)'); 
            if (!targetItem) {
                const items = document.querySelectorAll('.fi-fo-repeater-item');
                targetItem = items[items.length - 1];
            }
            if (!targetItem) return;
            let yawInput = targetItem.querySelector('input[wire\\:model*=\".yaw\"]');
            let pitchInput = targetItem.querySelector('input[wire\\:model*=\".pitch\"]');
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
            // Cập nhật chấm đỏ trên ảnh chung
            this.updateDots();
        },
        updateDots() {
            // Sẽ được Alpine tự cập nhật qua x-bind
        }
    }"
    class="space-y-2 mb-4"
>
    @if($panoramaUrl)
        <div class="text-xs text-gray-600 dark:text-gray-300">Click lên ảnh để đặt vị trí cho hotspot đang chọn (đang mở). Chấm đỏ sẽ hiện tương ứng.</div>
        <div class="relative border rounded-lg overflow-hidden bg-gray-50" style="padding:8px;text-align:center;">
            <div style="position:relative;display:inline-block;max-width:650px;width:100%;cursor:crosshair;" x-on:click="onImageClick($event)">
                <img
                    src="{{ $panoramaUrl }}"
                    alt="Panorama preview - chung"
                    class="block select-none"
                    draggable="false"
                    style="display:block;width:100%;height:auto;max-width:100%;max-height:320px;object-fit:contain;"
                />
                {{-- Hiện tất cả hotspots đã có --}}
                @foreach(($hotspots ?? []) as $index => $hotspot)
                    @if(isset($hotspot['yaw']) && isset($hotspot['pitch']) && $hotspot['yaw'] !== '' && $hotspot['pitch'] !== '')
                        <div
                            style="position:absolute;left:{{ ((floatval($hotspot['yaw'])+180)/360*100) }}%;top:{{ ((90-floatval($hotspot['pitch']))/180*100) }}%;width:14px;height:14px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 1px 4px rgba(0,0,0,0.4);pointer-events:none;"
                            title="{{ $hotspot['tooltip'] ?? 'Hotspot #'.($index+1) }}"
                        ></div>
                    @endif
                @endforeach
                <div class="absolute inset-0 pointer-events-none opacity-10" style="background-image: linear-gradient(to right,white 1px,transparent 1px),linear-gradient(to bottom,white 1px,transparent 1px);background-size:10% 10%;"></div>
            </div>
        </div>
        <div class="text-xs text-gray-500">Đã có {{ count($hotspots ?? []) }} hotspot(s). Mở 1 hotspot bên dưới rồi click lên ảnh chung để đặt vị trí.</div>
    @else
        <div class="text-xs text-amber-600 border border-amber-200 bg-amber-50 rounded p-2">
            Hãy upload ảnh Panorama ở trên trước, sau đó ảnh chung sẽ hiện ở đây để bạn click chọn vị trí cho tất cả hotspot.
        </div>
    @endif
</div>
