<div
    x-data="{
        show: false,
        stage: 'loading',
        count: 3,
        timer: null,
        showToast(e) {
            let title = e.detail?.title || e.detail?.[0]?.title || 'Thành công';
            this.show = true;
            this.stage = 'loading';
            this.count = 3;
            clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.count--;
                if (this.count <= 0) {
                    clearInterval(this.timer);
                    this.stage = 'success';
                    setTimeout(() => { this.show = false; this.stage = 'loading'; }, 1200);
                }
            }, 500);
        }
    }"
    x-on:show-centered-toast.window="showToast($event)"
    x-show="show"
    x-transition.opacity
    style="position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; background:rgba(0,0,0,0.4); backdrop-filter:blur(4px); padding:16px;"
    x-cloak
>
    <div style="background:white; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #f3f4f6; padding:32px; max-width:320px; width:100%; text-align:center; margin:auto;">
        <div x-show="stage === 'loading'">
            <div class="w-20 h-20 mx-auto relative flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-gray-200 dark:border-gray-600"></div>
                <div class="absolute inset-0 rounded-full border-4 border-amber-500 border-t-transparent animate-spin"></div>
                <span class="relative text-lg font-bold text-amber-600" x-text="count"></span>
            </div>
        </div>
        <div x-show="stage === 'success'" style="display: none;">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                <svg style="width:28px;height:28px" class="text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="font-semibold text-gray-900 dark:text-white">Thành công!</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Đã lưu cài đặt</p>
        </div>
    </div>
</div>
