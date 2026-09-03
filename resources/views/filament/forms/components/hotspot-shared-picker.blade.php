<script>window._hsInitialPanoramaUrl = @js($panoramaUrl);</script>
<div
    wire:ignore
    x-data="{
        panoramaUrl: window._hsInitialPanoramaUrl,
        selectedIndex: 0,
        hotspotItems: [],
        init() {
            // Khôi phục panoramaUrl triệt để: ưu tiên PHP, rồi sessionStorage, rồi DOM FileUpload preview
            const tryResolveFromDom = () => {
                const imgs = Array.from(document.querySelectorAll('img'));
                for (const img of imgs) {
                    const src = img.getAttribute('src') || img.src || '';
                    if (!src) continue;
                    // bỏ qua ảnh của chính picker (nằm trong wire:ignore)
                    if (img.closest('[wire\\:ignore]') && img.closest('[wire\\:ignore]') === this.$el) continue;
                    if (src.includes('panoramas') || src.includes('/storage/') || src.includes('blob:') || src.includes('/images/pana')) {
                        // đảm bảo không phải icon nhỏ, phải là ảnh panorama preview (thường nằm gần label Panorama Image)
                        if (img.width > 100 || img.naturalWidth > 100) return src;
                    }
                }
                return null;
            };
            if (!this.panoramaUrl) {
                const cached = sessionStorage.getItem('hs_panoramaUrl_' + window.location.pathname);
                if (cached) this.panoramaUrl = cached;
                else {
                    const domUrl = tryResolveFromDom();
                    if (domUrl) this.panoramaUrl = domUrl;
                }
            } else {
                sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, this.panoramaUrl);
            }
            // quan sát FileUpload preview thay đổi (khi upload mới)
            const domObserver = new MutationObserver(() => {
                const domUrl = tryResolveFromDom();
                if (domUrl && domUrl !== this.panoramaUrl) {
                    this.panoramaUrl = domUrl;
                    sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, domUrl);
                }
                this.refresh();
            });
            domObserver.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });

            this.refresh();
            this.$watch('selectedIndex', () => this.updateDots());
            this.$watch('panoramaUrl', (v) => { if (v) sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, v); });
            this.$nextTick(() => { this.refresh(); this.updateDots(); });
            // theo dõi khi repeater thêm/xóa item (Livewire re-render)
            const observer = new MutationObserver(() => this.refresh());
            observer.observe(document.body, { childList: true, subtree: true });
            // Livewire v3 hook nếu có
            if (window.Livewire) {
                try { Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => { succeed(({ snapshot, effect }) => { setTimeout(() => { const domUrl2 = tryResolveFromDom(); if (domUrl2 && !this.panoramaUrl) this.panoramaUrl = domUrl2; this.refresh(); }, 50); }); }); } catch(e) {}
                document.addEventListener('livewire:update', () => setTimeout(() => this.refresh(), 100));
            }
            setInterval(() => {
                if (!this.panoramaUrl) {
                    const domUrl = tryResolveFromDom();
                    if (domUrl) this.panoramaUrl = domUrl;
                }
                this.refresh();
            }, 800);
        },
        refresh() {
            // chỉ đếm yaw/pitch/tooltip của hotspot repeater, không tính Default View (data.yaw)
            const yawInputs = Array.from(document.querySelectorAll('input[wire\\:model*=&quot;hotspots&quot;][wire\\:model*=&quot;.yaw&quot;]'));
            const pitchInputs = Array.from(document.querySelectorAll('input[wire\\:model*=&quot;hotspots&quot;][wire\\:model*=&quot;.pitch&quot;]'));
            const tooltipInputs = Array.from(document.querySelectorAll('input[wire\\:model*=&quot;hotspots&quot;][wire\\:model*=&quot;.tooltip&quot;]'));
            if (!yawInputs.length) {
                if (this.hotspotItems.length !== 0) this.hotspotItems = [];
                this.$nextTick(() => { this.renameRepeaterHeaders(); this.injectRadiosIntoRepeater(); });
                return;
            }
            const items = yawInputs.map((el, i) => ({
                yaw: el.value,
                pitch: pitchInputs[i] ? pitchInputs[i].value : '',
                tooltip: tooltipInputs[i] ? tooltipInputs[i].value : '',
            }));
            const changed = JSON.stringify(items) !== JSON.stringify(this.hotspotItems);
            if (changed) {
                this.hotspotItems = items;
                if (this.selectedIndex >= items.length) this.selectedIndex = Math.max(0, items.length - 1);
                this.$nextTick(() => { this.updateDots(); this.renameRepeaterHeaders(); this.injectRadiosIntoRepeater(); });
            } else {
                this.renameRepeaterHeaders();
                this.injectRadiosIntoRepeater();
            }
        },
        injectRadiosIntoRepeater() {
            // Đưa radio vào ngay trong header của từng phần tử repeater
            const items = document.querySelectorAll('.fi-fo-repeater-item');
            items.forEach((item, idx) => {
                const header = item.querySelector('.fi-fo-repeater-item-header') || item.querySelector('[class*=repeater-item-header]') || item.firstElementChild;
                if (!header) return;
                let radio = header.querySelector(':scope > input.hs-inline-radio');
                if (!radio) {
                    radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'hs_selected_hotspot';
                    radio.className = 'hs-inline-radio w-4 h-4 text-primary-600 shrink-0 cursor-pointer';
                    radio.style.marginRight = '8px';
                    radio.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.selectedIndex = idx;
                        this.$nextTick(() => this.updateDots());
                    });
                    // chặn collapse khi click radio
                    radio.addEventListener('mousedown', (e) => e.stopPropagation());
                    header.prepend(radio);
                    // click vào header cũng chọn hotspot đó (không chỉ radio)
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', (e) => {
                        if (e.target.closest('button') || e.target.closest('[data-action]')) return;
                        if (e.target === radio) return;
                        this.selectedIndex = idx;
                        this.$nextTick(() => this.updateDots());
                    });
                }
                radio.checked = this.selectedIndex === idx;
                radio.value = idx;
                // highlight header đang chọn
                if (this.selectedIndex === idx) {
                    header.classList.add('!bg-primary-50');
                    header.style.background = '#eff6ff';
                    header.style.borderLeft = '3px solid #3b82f6';
                } else {
                    header.classList.remove('!bg-primary-50');
                    header.style.background = '';
                    header.style.borderLeft = '';
                }
            });
        },
        renameRepeaterHeaders() {
            // Đổi header của Repeater thành Hotspot 1, Hotspot 2 ...
            const items = document.querySelectorAll('.fi-fo-repeater-item');
            items.forEach((item, idx) => {
                // Filament v3: header label nằm trong .fi-fo-repeater-item-header hoặc span đầu tiên
                const label = item.querySelector('.fi-fo-repeater-item-header span, .fi-fo-repeater-item-header p, [data-repeater-item-label], h4');
                // fallback: tìm span có text Hotspot / Về / → Panorama
                const candidates = item.querySelectorAll('span, p, div');
                let target = label;
                if (!target) {
                    for (const c of candidates) {
                        const t = c.textContent.trim();
                        if (t && (t.startsWith('Hotspot') || t.startsWith('Về') || t.startsWith('→') || t === 'Hotspot mới')) { target = c; break; }
                    }
                }
                if (target) {
                    const tooltip = this.hotspotItems[idx]?.tooltip;
                    const newLabel = tooltip ? `Hotspot ${idx+1} — ${tooltip}` : `Hotspot ${idx+1}`;
                    if (target.textContent.trim() !== newLabel) target.textContent = newLabel;
                }
            });
        },
        normalizedLeft(yaw) {
            const y = parseFloat(yaw);
            if (isNaN(y)) return 50;
            let n = (y + 180) % 360;
            if (n < 0) n += 360;
            return (n / 360 * 100);
        },
        normalizedTop(pitch) {
            const p = parseFloat(pitch);
            if (isNaN(p)) return 50;
            return ((90 - p) / 180 * 100);
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
                } else {
                    el.style.opacity = '0.7';
                    el.style.transform = 'translate(-50%,-50%) scale(1)';
                    el.style.zIndex = '10';
                    el.style.boxShadow = '0 1px 4px rgba(0,0,0,0.4)';
                    el.style.borderColor = '#fff';
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
            const yawInputs = Array.from(document.querySelectorAll('input[wire\\:model*=&quot;hotspots&quot;][wire\\:model*=&quot;.yaw&quot;]'));
            const pitchInputs = Array.from(document.querySelectorAll('input[wire\\:model*=&quot;hotspots&quot;][wire\\:model*=&quot;.pitch&quot;]'));
            let yawInput = null, pitchInput = null;
            if (this.selectedIndex !== null && yawInputs[this.selectedIndex]) {
                yawInput = yawInputs[this.selectedIndex];
                pitchInput = pitchInputs[this.selectedIndex];
            } else if (yawInputs.length) {
                yawInput = yawInputs[yawInputs.length - 1];
                pitchInput = pitchInputs[pitchInputs.length - 1];
                this.selectedIndex = yawInputs.length - 1;
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
            setTimeout(() => { this.refresh(); this.updateDots(); }, 80);
        }
    }"
    class="space-y-2 mb-4"
    x-init="if (!panoramaUrl) { const cached = sessionStorage.getItem('hs_panoramaUrl_' + window.location.pathname); if (cached) panoramaUrl = cached; } else { sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, panoramaUrl); } $watch('panoramaUrl', v => { if (v) sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, v); })"
>
    <div x-show="panoramaUrl" x-cloak>
        <div class="text-xs text-gray-600 dark:text-gray-300">Chọn hotspot bằng radio bên dưới, sau đó click lên ảnh để đặt vị trí. Chấm viền xanh là hotspot đang chọn (hỗ trợ nhiều chấm đỏ).</div>
        <div class="relative border rounded-lg overflow-hidden bg-gray-50 p-2 flex justify-center">
            <div style="position:relative;display:inline-block;cursor:crosshair;max-width:650px;" x-on:click="onImageClick($event)">
                <img
                    :src="panoramaUrl"
                    src="{{ $panoramaUrl }}"
                    alt="Panorama preview - chung"
                    class="block select-none"
                    draggable="false"
                    style="display:block;max-width:100%;width:auto;height:auto;max-height:320px;object-fit:contain;"
                />
                {{-- Dots render bằng JS (x-for) để luôn đồng bộ với repeater, PHP fallback cho SSR --}}
                <template x-for="(hs, idx) in hotspotItems" :key="idx">
                    <div
                        x-show="hs.yaw !== '' && hs.yaw !== null && hs.pitch !== '' && hs.pitch !== null"
                        :data-hotspot-dot="idx"
                        :title="(hs.tooltip || ('Hotspot #' + (idx+1))) + ' (yaw:' + hs.yaw + ', pitch:' + hs.pitch + ')'"
                        :style="`position:absolute;left:${normalizedLeft(hs.yaw)}%;top:${normalizedTop(hs.pitch)}%;width:14px;height:14px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 1px 4px rgba(0,0,0,0.4);pointer-events:none;opacity:0.7;`"
                    ></div>
                </template>
                <div id="shared-hotspot-preview-dot" style="display:none;position:absolute;width:14px;height:14px;background:#22c55e;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:20;box-shadow:0 1px 6px rgba(0,0,0,0.5);pointer-events:none;"></div>
            </div>
        </div>

        <div class="text-xs text-gray-500" x-text="`Đã có ${hotspotItems.length} hotspot(s). Radio đã chuyển vào trong từng Hotspot bên dưới — click radio hoặc header để chọn, rồi click lên ảnh để đặt Yaw/Pitch.`"></div>
        <div x-show="hotspotItems.length === 0" class="text-xs text-amber-600 border border-amber-200 bg-amber-50 rounded p-2">Chưa có hotspot nào. Bấm “Thêm hotspot” bên dưới, sau đó chọn radio trong Hotspot và click lên ảnh để đặt vị trí.</div>

        {{-- Fallback SSR dots khi JS chưa kịp load (ẩn khi Alpine ready) --}}
        <noscript>
            @foreach(($hotspots ?? []) as $index => $hotspot)
                @if(isset($hotspot['yaw']) && isset($hotspot['pitch']) && $hotspot['yaw'] !== '' && $hotspot['pitch'] !== '')
                    @php
                        $yaw = floatval($hotspot['yaw']);
                        $pitch = floatval($hotspot['pitch']);
                        $n = fmod($yaw + 180, 360); if ($n < 0) $n += 360; $left = $n / 360 * 100;
                        $top = (90 - $pitch) / 180 * 100;
                    @endphp
                    <div style="display:none"></div>
                @endif
            @endforeach
        </noscript>
    </div>
    <div x-show="!panoramaUrl" x-cloak class="text-xs text-amber-600 border border-amber-200 bg-amber-50 rounded p-2">
        Hãy upload ảnh Panorama ở trên trước, sau đó ảnh chung sẽ hiện ở đây để bạn click chọn vị trí cho tất cả hotspot.
    </div>
    {{-- Giữ @if cho SSR lần đầu nếu JS chưa load --}}
    @if(!$panoramaUrl)
        <div x-show="!panoramaUrl" class="hidden"></div>
    @endif
</div>
