<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border bg-white p-4 dark:bg-gray-900 dark:border-gray-700">
            <h3 class="font-semibold mb-2">Trạng thái Migration</h3>
            <p class="text-sm text-gray-500 mb-3">Chạy <code>migrate:status</code> để xem migrations nào chưa chạy. Dùng nút <b>Chạy Migrate</b> ở header để chạy <code>php artisan migrate --force</code> trực tiếp trên hosting (InfinityFree không có SSH).</p>
            <pre class="bg-gray-950 text-gray-100 p-4 rounded-lg text-xs overflow-auto max-h-96 whitespace-pre-wrap">{{ $this->statusOutput ?? 'Đang tải...' }}</pre>
        </div>

        @if($this->output)
        <div class="rounded-xl border bg-white p-4 dark:bg-gray-900 dark:border-gray-700">
            <h3 class="font-semibold mb-2">Kết quả lần chạy gần nhất</h3>
            <pre class="bg-gray-950 text-gray-100 p-4 rounded-lg text-xs overflow-auto max-h-96 whitespace-pre-wrap">{{ $this->output }}</pre>
        </div>
        @endif

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:bg-amber-900/20 dark:border-amber-800">
            <h4 class="font-semibold text-amber-800 dark:text-amber-200 text-sm">Lưu ý</h4>
            <ul class="list-disc ml-5 text-sm text-amber-700 dark:text-amber-300 mt-1 space-y-1">
                <li>Trang này chỉ dành cho admin đã đăng nhập.</li>
                <li>Nếu host không hỗ trợ JSON, fallback sẽ tự tạo cột dạng TEXT.</li>
                <li>Sau khi chạy, có thể vào <a href="/run-migrate?token=pano-migrate-2026" class="underline">/run-migrate?token=...</a> để kiểm tra lại bằng API.</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
