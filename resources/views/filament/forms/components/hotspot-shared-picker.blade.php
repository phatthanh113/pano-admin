<div
    x-data="{
        selectedIndex: 0,
        init() {
            this.$watch('selectedIndex', () => this.updateDots());
            this.$nextTick(() => this.updateDots());
        },
        updateDots() {
            document.querySelectorAll('[data-hotspot-dot]').forEach(el => {
                const idx = parseInt(el.getAttribute('data-hotspot-dot'));
                if (idx === this.selectedIndex) {
                    el.style.opacity = '1';
                    el.style.transform = 'translate(-50%,-50%) scale(1.25)';
                    el.style.zIndex = '12';
                    el.style.boxShadow = '0 0 0 3px rgba(34,197,94,0.45), 0 1px 6px rgba(0,0,0,0.5)';
                    el.style.borderColor = '#22c55e';
                    el.style.background = '#dc2626';
                } else {
                    el.style.opacity = '0.7';
                    el.style.transform = 'translate(-50%,-50%) scale(1)';
                    el.style.zIndex = '10';
                    el.style.boxShadow = '0 1px 4px rgba(0,0,0,0.4)';
                    el.style.borderColor = '#fff';
                    el.style.background = '#dc2626';
                }
            });
        },
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

            // Use selectedIndex to target the correct repeater item
            const yawInputs = Array.from(document.querySelectorAll('input[wire\\:model*=\".yaw\"]'));
            const pitchInputs = Array.from(document.querySelectorAll('input[wire\\:model*=\".pitch\"]'));
            let yawInput = null, pitchInput = null;
            if (this.selectedIndex !== null && yawInputs[this.selectedIndex]) {
                yawInput = yawInputs[this.selectedIndex];
                pitchInput = pitchInputs[this.selectedIndex];
            } else if (yawInputs.length) {
                yawInput = yawInputs[yawInputs.length - 1];
                pitchInput = pitchInputs[pitchInputs.length - 1];
            }

            if (yawInput) {
                yawInput.focus();
                yawInput.value = newYaw;
                yawInput.dispatchEvent(new Event('input', { bubbles: true }));
                yawInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (pitchInput) {
                pitchInput.value = newPitch;
                pitchInput.dispatchEvent(new Event('input', { bubbles: true }));
                pitchInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const previewDot = document.getElementById('shared-hotspot-preview-dot');
            if (previewDot) {
                previewDot.style.left = `${(x / w) * 100}%`;
                previewDot.style.top = `${(y / h) * 100}%`;
                previewDot.style.display = 'block';
            }
            // also update the selected dot immediately for visual feedback
            setTimeout(() => this.updateDots(), 50);
        }
    }"
    class="space-y-2 mb-4"
>
    @if($panoramaUrl)
        <div class="text-xs text-gray-600 dark:text-gray-300">Chọn hotspot bằng radio bên dưới, sau đó click lên ảnh để đặt vị trí. Chấm viền xanh là hotspot đang chọn.</div>
        <div class="relative border rounded-lg overflow-hidden bg-gray-50 p-2 flex justify-center">
            <div style="position:relative;display:inline-block;cursor:crosshair;max-width:650px;" x-on:click="onImageClick($event)">
                <img
                    src="{{ $panoramaUrl }}"
                    alt="Panorama preview - chung"
                    class="block select-none"
                    draggable="false"
                    style="display:block;max-width:100%;width:auto;height:auto;max-height:320px;object-fit:contain;"
                />
                @foreach(($hotspots ?? []) as $index => $hotspot)
                    @if(isset($hotspot['yaw']) && isset($hotspot['pitch']) && $hotspot['yaw'] !== '' && $hotspot['pitch'] !== '')
                        <div
                            data-hotspot-dot="{{ $index }}"
                            style="position:absolute;left:{{ ((floatval($hotspot['yaw'])+180)/360*100) }}%;top:{{ ((90-floatval($hotspot['pitch']))/180*100) }}%;width:14px;height:14px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 1px 4px rgba(0,0,0,0.4);pointer-events:none;opacity:0.7;"
                            title="{{ $hotspot['tooltip'] ?? 'Hotspot #'.($index+1) }} (yaw:{{ $hotspot['yaw'] }}, pitch:{{ $hotspot['pitch'] }})"
                        ></div>
                    @endif
                @endforeach
                <div id="shared-hotspot-preview-dot" style="display:none;position:absolute;width:14px;height:14px;background:#22c55e;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:20;box-shadow:0 1px 6px rgba(0,0,0,0.5);pointer-events:none;"></div>
            </div>
        </div>

        @if(count($hotspots ?? []) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach(($hotspots ?? []) as $index => $hotspot)
                    <label
                        @click="selectedIndex = {{ $index }}; updateDots()"
                        :class="selectedIndex === {{ $index }} ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900/30' : 'bg-white border-gray-200 hover:border-gray-300 dark:bg-gray-800 dark:border-gray-700'"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border cursor-pointer text-xs font-medium transition-colors"
                    >
                        <input type="radio" name="selected_hotspot_shared" value="{{ $index }}" :checked="selectedIndex === {{ $index }}" @change="selectedIndex = {{ $index }}" class="w-3.5 h-3.5 text-primary-600 focus:ring-primary-500">
                        <span>{{ $hotspot['tooltip'] ?? 'Hotspot #'.($index+1) }}</span>
                        <span class="w-2 h-2 rounded-full" :style="selectedIndex === {{ $index }} ? 'background:#22c55e' : 'background:#d1d5db'"></span>
                    </label>
                @endforeach
            </div>
            <div class="text-xs text-gray-500">Đã có {{ count($hotspots ?? []) }} hotspot(s). Radio đang chọn sẽ được cập nhật khi click lên ảnh.</div>
        @else
            <div class="text-xs text-gray-500">Chưa có hotspot nào. Bấm “Thêm hotspot” bên dưới, sau đó chọn radio và click lên ảnh để đặt vị trí.</div>
        @endif
    @else
        <div class="text-xs text-amber-600 border border-amber-200 bg-amber-50 rounded p-2">
            Hãy upload ảnh Panorama ở trên trước, sau đó ảnh chung sẽ hiện ở đây để bạn click chọn vị trí cho tất cả hotspot.
        </div>
    @endif
</div>
