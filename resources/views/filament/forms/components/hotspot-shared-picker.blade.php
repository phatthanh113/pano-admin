<script>
(function() {
  const _pnUrl = @js($panoramaUrl);
  if (_pnUrl) window._hsInitialPanoramaUrl = _pnUrl;
  else if (!window._hsInitialPanoramaUrl) window._hsInitialPanoramaUrl = _pnUrl;
})();
window._hsPanoramaMap = @js($panoramaMap ?? []);
window._hsI18n = @js([
    'hotspot' => __('forms.hotspot'),
    'hotspot_new' => __('forms.hotspot_new'),
    'hotspot_not_selected' => __('forms.hotspot_not_selected'),
    'hotspot_choose_hint' => __('forms.hotspot_choose_hint'),
    'hotspot_count' => __('forms.hotspot_count'),
    'hotspot_no_hotspot' => __('forms.hotspot_no_hotspot'),
    'hotspot_upload_first' => __('forms.hotspot_upload_first'),
    'hotspot_header' => __('forms.hotspot_header'),
    'delete' => __('forms.delete'),
]);
</script>
<div
    x-data="{
        panoramaUrl: window._hsInitialPanoramaUrl,
        panoramaMap: window._hsPanoramaMap || {},
        selectedIndex: 0,
        hotspotItems: [],
        showDeleteModal: false,
        toastMessage: '',
        showToast: false,
        init() {
            // Khôi phục panoramaUrl triệt để: ưu tiên PHP, rồi hidden current_panorama_url, rồi sessionStorage, rồi DOM FileUpload preview
            const tryResolveFromDom = () => {
                const hidden = document.querySelector('input[id*=current_panorama_url]');
                if (hidden && hidden.value) return hidden.value;
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
            this.$watch('showDeleteModal', (val) => {
                if (val) this.$nextTick(() => {
                    const m = document.querySelector('[data-hs-modal]');
                    if (m && m.parentElement !== document.body) document.body.appendChild(m);
                    const t = document.querySelector('[data-hs-toast]');
                    if (t && t.parentElement !== document.body) document.body.appendChild(t);
                });
            });
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
            // Lấy dữ liệu từng repeater item trực tiếp, tránh lỗi wire:model selector
            const repeaterEls = Array.from(document.querySelectorAll('.fi-fo-repeater-item'));
            if (!repeaterEls.length) {
                if (this.hotspotItems.length !== 0) this.hotspotItems = [];
                this.$nextTick(() => { this.renameRepeaterHeaders(); this.injectRadiosIntoRepeater(); });
                return;
            }
            const items = repeaterEls.map((itemEl) => {
                const yawEl = itemEl.querySelector('input[id*=yaw], input[name*=yaw]');
                const pitchEl = itemEl.querySelector('input[id*=pitch], input[name*=pitch]');
                const tooltipEl = itemEl.querySelector('input[id*=tooltip], input[name*=tooltip]');
                const targetEl = itemEl.querySelector('select, input[id*=target_panorama_id], [id*=target_panorama_id]');
                // TomSelect có thể lưu value trong select hidden
                let targetVal = '';
                if (targetEl) {
                    targetVal = targetEl.value || '';
                    // nếu là TomSelect, value vẫn nằm ở select
                    if (!targetVal) {
                        const tsControl = itemEl.querySelector('.ts-control');
                        if (tsControl) {
                            const tsItem = tsControl.querySelector('.item');
                            if (tsItem && tsItem.dataset.value) targetVal = tsItem.dataset.value;
                        }
                    }
                }
                return {
                    yaw: yawEl ? yawEl.value : '',
                    pitch: pitchEl ? pitchEl.value : '',
                    tooltip: tooltipEl ? tooltipEl.value : '',
                    target_panorama_id: targetVal,
                };
            });
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
            // Đổi header thành Hotspot 1 - Target Panorama
            const items = document.querySelectorAll('.fi-fo-repeater-item');
            items.forEach((item, idx) => {
                const label = item.querySelector('.fi-fo-repeater-item-header span, .fi-fo-repeater-item-header p, [data-repeater-item-label], h4');
                const candidates = item.querySelectorAll('span, p, div');
                let target = label;
                if (!target) {
                    for (const c of candidates) {
                        const t = c.textContent.trim();
                        if (t && (t.startsWith('Hotspot') || t.startsWith('Về') || t.startsWith('→') || t === 'Hotspot mới' || t.includes('—'))) { target = c; break; }
                    }
                }
                if (target) {
                    let targetName = '';
                    const tid = this.hotspotItems[idx]?.target_panorama_id;
                    if (tid && this.panoramaMap && this.panoramaMap[tid]) {
                        targetName = this.panoramaMap[tid];
                    } else {
                        const tsItem = item.querySelector('.ts-control .item, .choices__item, [data-ts-item]');
                        if (tsItem && tsItem.textContent.trim()) {
                            targetName = tsItem.textContent.trim();
                        } else {
                            const sel = item.querySelector('select');
                            if (sel && sel.selectedOptions.length && sel.selectedOptions[0].value) {
                                targetName = sel.selectedOptions[0].textContent.trim();
                            } else {
                                const inp = item.querySelector('[wire\\:model*=&quot;target_panorama_id&quot;]');
                                if (inp && inp.value) targetName = this.panoramaMap[inp.value] || `Panorama #${inp.value}`;
                            }
                        }
                    }
                    const notSel = (window._hsI18n && window._hsI18n.hotspot_not_selected) || 'Chưa chọn';
                    let newLabel;
                    if (!targetName || targetName === notSel || targetName === 'Not selected' || targetName === '未選択') {
                        const base = (window._hsI18n && window._hsI18n.hotspot) || 'Hotspot';
                        newLabel = `${base} ${idx+1}`;
                    } else {
                        let tmpl = (window._hsI18n && window._hsI18n.hotspot_header) || 'Hotspot :index - :target';
                        newLabel = tmpl.replace(':index', idx+1).replace(':target', targetName);
                    }
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
            const repeaterForClick = Array.from(document.querySelectorAll('.fi-fo-repeater-item'));
            let yawInput = null, pitchInput = null;
            if (this.selectedIndex !== null && repeaterForClick[this.selectedIndex]) {
                yawInput = repeaterForClick[this.selectedIndex].querySelector('input[id*=yaw], input[name*=yaw]');
                pitchInput = repeaterForClick[this.selectedIndex].querySelector('input[id*=pitch], input[name*=pitch]');
            } else if (repeaterForClick.length) {
                const last = repeaterForClick[repeaterForClick.length - 1];
                yawInput = last.querySelector('input[id*=yaw], input[name*=yaw]');
                pitchInput = last.querySelector('input[id*=pitch], input[name*=pitch]');
                this.selectedIndex = repeaterForClick.length - 1;
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
        },
        showToastMessage(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 3000);
        },
        confirmDelete() {
            this.showDeleteModal = true;
        },
        executeDelete() {
            this.showDeleteModal = false;
            const items = document.querySelectorAll('.fi-fo-repeater-item');
            const target = items[this.selectedIndex];
            if (!target) return;
            const base = (window._hsI18n && window._hsI18n.hotspot) || 'Hotspot';
            const delLabel = `${base} ${this.selectedIndex+1}`;
            // Thử xóa trực tiếp qua Livewire (đáng tin cậy nhất) - hỗ trợ cả array và object
            let deletedViaWire = false;
            const tryWireDelete = (wireObj) => {
                try {
                    let d = wireObj.get('data.hotspots');
                    if (!d) d = wireObj.get('hotspots');
                    if (!d && wireObj.data) d = wireObj.data.hotspots;
                    if (!d) return false;
                    if (Array.isArray(d)) {
                        if (d.length > this.selectedIndex) {
                            d.splice(this.selectedIndex, 1);
                            wireObj.set('data.hotspots', d);
                            return true;
                        }
                    } else if (typeof d === 'object') {
                        const keys = Object.keys(d);
                        const k = keys[this.selectedIndex];
                        if (k !== undefined) {
                            delete d[k];
                            // Livewire cần set lại object để trigger update
                            wireObj.set('data.hotspots', {...d});
                            return true;
                        }
                    }
                } catch(e) {}
                return false;
            };
            try {
                if (this.$wire) deletedViaWire = tryWireDelete(this.$wire);
                if (!deletedViaWire && window.Livewire) {
                    const liveEl = document.querySelector('[wire\\:id]');
                    if (liveEl && window.Livewire.find) {
                        try {
                            const comp = window.Livewire.find(liveEl.getAttribute('wire:id'));
                            if (comp) deletedViaWire = tryWireDelete(comp);
                        } catch(e) {}
                    }
                }
            } catch(e) { console.log('wire delete failed', e); }
            if (deletedViaWire) {
                this.showToastMessage(`${(window._hsI18n && window._hsI18n.delete) || 'Đã xóa'} ${delLabel}`);
                setTimeout(() => { this.refresh(); this.updateDots(); if (this.selectedIndex >= document.querySelectorAll('.fi-fo-repeater-item').length) this.selectedIndex = Math.max(0, document.querySelectorAll('.fi-fo-repeater-item').length - 1); }, 300);
                return;
            }
            // Fallback: click nút thùng rác trong header
            let delBtn = null;
            const header = target.querySelector('.fi-fo-repeater-item-header');
            if (header) {
                const btns = header.querySelectorAll('button');
                for (let i = btns.length - 1; i >= 0; i--) {
                    const b = btns[i];
                    const html = b.innerHTML || '';
                    if (html.includes('M19 7') || html.includes('trash') || b.querySelector('svg') || (b.getAttribute('wire:click') && b.getAttribute('wire:click').includes('delete')) || b.getAttribute('title')?.toLowerCase().includes('delete')) {
                        delBtn = b; break;
                    }
                }
                if (!delBtn && btns.length) delBtn = btns[btns.length - 1];
            }
            if (!delBtn) delBtn = target.querySelector('button');
            if (delBtn) {
                delBtn.click();
                this.showToastMessage(`${(window._hsI18n && window._hsI18n.delete) || 'Đã xóa'} ${delLabel}`);
                setTimeout(() => { this.refresh(); this.updateDots(); if (this.selectedIndex >= document.querySelectorAll('.fi-fo-repeater-item').length) this.selectedIndex = Math.max(0, document.querySelectorAll('.fi-fo-repeater-item').length - 1); }, 300);
            }
        },
        deleteSelected() { this.confirmDelete(); }
    }"
     class="space-y-2 mb-4"
    x-init="if (!panoramaUrl) { const cached = sessionStorage.getItem('hs_panoramaUrl_' + window.location.pathname); if (cached) panoramaUrl = cached; } else { sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, panoramaUrl); } $watch('panoramaUrl', v => { if (v) sessionStorage.setItem('hs_panoramaUrl_' + window.location.pathname, v); })"
>
    <div wire:ignore>
    <div x-show="panoramaUrl" x-cloak>
        <div class="text-xs text-gray-600 dark:text-gray-300">{{ __('forms.hotspot_choose_hint') }}</div>
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
                        :title="(hs.tooltip || ((window._hsI18n && window._hsI18n.hotspot || 'Hotspot') + ' #' + (idx+1))) + ' (yaw:' + hs.yaw + ', pitch:' + hs.pitch + ')'"
                        :style="`position:absolute;left:${normalizedLeft(hs.yaw)}%;top:${normalizedTop(hs.pitch)}%;width:14px;height:14px;background:#dc2626;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:10;box-shadow:0 1px 4px rgba(0,0,0,0.4);pointer-events:none;opacity:0.7;`"
                    ></div>
                </template>
                <div id="shared-hotspot-preview-dot" style="display:none;position:absolute;width:14px;height:14px;background:#22c55e;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);z-index:20;box-shadow:0 1px 6px rgba(0,0,0,0.5);pointer-events:none;"></div>
            </div>
        </div>

        <div class="text-xs text-gray-500" x-text="(window._hsI18n && window._hsI18n.hotspot_count ? window._hsI18n.hotspot_count.replace(':count', hotspotItems.length) : `Đã có ${hotspotItems.length} hotspot(s).`)"></div>
        <div x-show="hotspotItems.length" style="display:flex;align-items:center;gap:8px;margin-top:4px;">
            <button type="button" @click="deleteSelected()" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;font-size:12px;font-weight:500;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span x-text="`${(window._hsI18n && window._hsI18n.delete) || 'Xóa'} ${(window._hsI18n && window._hsI18n.hotspot || 'Hotspot')} ${selectedIndex+1}`"></span>
            </button>
        </div>
        <div x-show="hotspotItems.length === 0" class="text-xs text-amber-600 border border-amber-200 bg-amber-50 rounded p-2">{{ __('forms.hotspot_no_hotspot') }}</div>

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
        {{ __('forms.hotspot_upload_first') }}
    </div>
    {{-- Giữ @if cho SSR lần đầu nếu JS chưa load --}}
    @if(!$panoramaUrl)
        <div x-show="!panoramaUrl" class="hidden"></div>
    @endif
    </div>

    <!-- Delete Confirm Modal - đẹp -->
    <div x-show="showDeleteModal" x-cloak data-hs-modal x-transition.opacity style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:9999;" @click.self="showDeleteModal = false">
        <div style="background:white;border-radius:12px;padding:24px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.25);">
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:16px;">
                <div style="width:40px;height:40px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#dc2626;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0;font-weight:600;color:#111827;font-size:15px;" x-text="((window._hsI18n && window._hsI18n.delete) || 'Xóa') + ' ' + ((window._hsI18n && window._hsI18n.hotspot) || 'Hotspot') + ' ' + (selectedIndex+1) + '?'"></h3>
                    <p style="margin:4px 0 0;font-size:13px;color:#6b7280;">Bạn có chắc muốn xóa hotspot này? Hành động không thể hoàn tác.</p>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" @click="showDeleteModal = false" style="padding:8px 16px;border-radius:8px;border:1px solid #e5e7eb;background:white;font-size:13px;cursor:pointer;">Hủy</button>
                <button type="button" @click="executeDelete()" style="padding:8px 16px;border-radius:8px;background:#dc2626;color:white;border:1px solid #dc2626;font-size:13px;font-weight:500;cursor:pointer;">{{ __('forms.delete') }}</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-show="showToast" x-transition x-cloak data-hs-toast style="position:fixed;top:20px;right:20px;background:#10b981;color:white;padding:12px 20px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:9999;display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span x-text="toastMessage"></span>
    </div>
    </template>
</div>
