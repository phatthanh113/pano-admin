# Deploy Pano — Same Origin (Không CORS)

## Kiến trúc
```
Browser -> Laravel (single origin)
  /            -> React SPA (public/pano/index.html)  — build từ D:\pano
  /admin       -> Filament Admin
  /api/*       -> JSON API cho React
  /storage/*   -> ảnh panorama / planImage
  /up          -> health check
```
Không CORS vì frontend và backend cùng domain. Dev dùng Vite proxy để giả lập same-origin.

## Dev local
1. Laravel: `php artisan serve` hoặc Laragon `http://pano-admin.test`
2. React: `cd D:\pano && npm run dev` (Vite proxy /api + /storage -> pano-admin.test)

`.env` frontend (`D:\pano\.env`): `VITE_API_BASE_URL=` (rỗng = same-origin qua proxy)

## Build & Deploy local test
```powershell
cd D:\pano
npm run deploy
# hoặc thủ công:
powershell -ExecutionPolicy Bypass -File ./deploy.ps1
```
Sau đó truy cập `http://pano-admin.test/` sẽ thấy React (không cần `npm run dev`).

## Deploy lên hosting (shared/VPS)

### Option A: Single origin (khuyến nghị — không CORS)
1. Trên local chạy `npm run deploy` để tạo `pano-admin/public/pano`
2. Upload toàn bộ thư mục `pano-admin` lên hosting (ví dụ `public_html` trỏ vào `public/`)
3. Trên hosting:
```bash
composer install --no-dev --optimize-autoloader
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
# đảm bảo APP_URL=https://yourdomain.com trong .env
```
4. Trỏ domain về `public/` — xong. Frontend ở `/`, admin ở `/admin`.

### Option B: Tách domain (frontend Vercel/Netlify, backend riêng)
1. Set `VITE_API_BASE_URL=https://api.yourdomain.com` trong `D:\pano\.env.production`
2. Set `CORS_ALLOWED_ORIGINS=https://yourdomain.com` trong `pano-admin/.env`
3. Build frontend deploy riêng, backend deploy riêng.
4. CORS đã config trong `config/cors.php` cho `api/*`.

## Seed data test
Nếu DB trống, API trả rỗng -> frontend tự fallback về `projectsData.js`. Để test API có data:
```bash
php artisan migrate:fresh --seed # nếu có seeder
# hoặc thêm thủ công trong /admin
```
Check: `GET http://pano-admin.test/api/projects` và `GET http://pano-admin.test/api/health`

## Lưu ý storage
- Ảnh upload qua Filament lưu vào `storage/app/public/panoramas/*`
- URL trả về `/storage/panoramas/...` — Vite proxy và Laravel cùng serve, không CORS.
- Chạy `php artisan storage:link` để tạo symlink `public/storage`.
