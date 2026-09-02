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
            let yawInput = null, pitchInput = null;
            const visibleItems = Array.from(document.querySelectorAll(&quot;.fi-fo-repeater-item&quot;)).filter(item => {
                const content = item.querySelector(&quot;.fi-fo-repeater-item-content&quot;);
                return content && content.offsetParent !== null && content.offsetHeight > 10;
            });
            if (visibleItems.length === 1) {
                yawInput = visibleItems[0].querySelector(&quot;input[wire\\:model*=&quot;.yaw&quot;]&quot;);
                pitchInput = visibleItems[0].querySelector(&quot;input[wire\\:model*=&quot;.pitch&quot;]&quot;);
            } else if (visibleItems.length > 1) {
                const lastVisible = visibleItems[visibleItems.length - 1];
                yawInput = lastVisible.querySelector(&quot;input[wire\\:model*=&quot;.yaw&quot;]&quot;);
                pitchInput = lastVisible.querySelector(&quot;input[wire\\:model*=&quot;.pitch&quot;]&quot;);
            } else {
                const allYaws = Array.from(document.querySelectorAll(&quot;input[wire\\:model*=&quot;.yaw&quot;]&quot;)).filter(el => el.offsetParent !== null);
                const allPitches = Array.from(document.querySelectorAll(&quot;input[wire\\:model*=&quot;.pitch&quot;]&quot;)).filter(el => el.offsetParent !== null);
                if (allYaws.length) yawInput = allYaws[allYaws.length - 1];
                if (allPitches.length) pitchInput = allPitches[allPitches.length - 1];
            }
            if (!yawInput) {
                const items = document.querySelectorAll(&quot;.fi-fo-repeater-item&quot;);
                const last = items[items.length - 1];
                if (last) {
                    yawInput = last.querySelector(&quot;input[wire\\:model*=&quot;.yaw&quot;]&quot;);
                    pitchInput = last.querySelector(&quot;input[wire\\:model*=&quot;.pitch&quot;]&quot;);
                }
            }
            if (yawInput) {
                yawInput.focus();
                yawInput.value = newYaw;
                yawInput.dispatchEvent(new Event(&quot;input&quot;, { bubbles: true }));
                yawInput.dispatchEvent(new Event(&quot;change&quot;, { bubbles: true }));
            }
            if (pitchInput) {
                pitchInput.value = newPitch;
                pitchInput.dispatchEvent(new Event(&quot;input&quot;, { bubbles: true }));
                pitchInput.dispatchEvent(new Event(&quot;change&quot;, { bubbles: true }));
            }
            const previewDot = document.getElementById(&quot;shared-hotspot-preview-dot&quot;);
            if (previewDot) {
                previewDot.style.left = `${(x / w) * 100}%`;
                previewDot.style.top = `${(y / h) * 100}%`;
                previewDot.style.display = &quot;block&quot;;
            }
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
                            style="position:absolute;left:{{ ((floatval($hotspot['yaw'])+180)/360*100) }}%;top:{{ ((90-floatval($hotspot['pitch']))/180*100) }}%;width:14px;height:14px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 1px 4px rgba(0,0,0,0.4);pointer-events:none;opacity:0.7;"
                            title="{{ $hotspot['tooltip'] ?? 'Hotspot #'.($index+1) }}"
                        ></div>
                    @endif
                @endforeach
                <div id="shared-hotspot-preview-dot" style="display:none;position:absolute;width:14px;height:14px;background:#22c55e;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:20;box-shadow:0 1px 6px rgba(0,0,0,0.5);pointer-events:none;"></div>
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
