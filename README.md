# Pano Admin - Laravel + Filament + React Viewer

> Hệ thống quản lý Panorama 360° với Admin Filament và Frontend React (Photo Sphere Viewer). Frontend React được build vào `public/pano` để chạy same-origin, không CORS.

---

## 🇻🇳 Tiếng Việt

### 1. Kiến trúc
- **Backend:** `pano-admin` (Laravel 11 + Filament 3) - quản lý Project / Building / Floor / Panorama / Hotspot / Video / User / SiteSetting. API: `routes/api.php` (`/api/projects`, `/api/site-settings`, `/api/auth/*`)
- **Frontend:** `D:\pano` (React 19 + Vite 5 + Three.js + Photo Sphere Viewer) - build ra `dist/` rồi `deploy.ps1` copy vào `pano-admin/public/pano` + `public/assets`. Truy cập: `http://pano-admin.test/` (panorama) vs `http://pano-admin.test/admin` (Filament)
- **Deploy:** GitHub Actions `.github/workflows/deploy.yml` tự `npm run build` và FTP lên InfinityFree `ftpupload.net` -> `/htdocs`

### 2. Yêu cầu
- PHP 8.2+, Composer, Node 20+, MySQL 8, Laragon/XAMPP hoặc `php artisan serve`

### 3. Cài đặt khi clone về

```bash
git clone https://github.com/phatthanh113/pano-admin.git
cd pano-admin
cp .env.example .env
# Sửa .env: APP_URL, DB_*, FILESYSTEM_DISK=public
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

**Frontend React (nếu sửa giao diện pano):**
```bash
cd D:\pano
npm install
npm run deploy   # build + copy dist -> D:\laragon\www\pano-admin\public\pano
# hoặc npm run dev để dev riêng (Vite proxy /api -> http://pano-admin.test)
```

### 4. Tài khoản mẫu
| ID | Password | Role |
|---|---|---|
| `admin` | `admin123` (hoặc trong seeder) | `admin` - vào được `/admin` |
| `demo` | `demo123` | `user` |
| `viewer` | `viewer123` | `user` |

Tạo user mới qua Filament `Users` hoặc `php artisan tinker`.

### 5. Chạy local
```bash
# Terminal 1: Laravel
php artisan serve  # hoặc Laragon -> http://pano-admin.test

# Terminal 2: nếu sửa admin CSS/JS
npm run dev
```

### 6. Deploy lên InfinityFree (hoặc host khác)

**Cách A - Tự động qua GitHub Actions (khuyên dùng):**
1. Tạo repo trống trên GitHub, push code lên
2. Vào `Settings` -> `Secrets and variables` -> `Actions` -> tạo 4 Secrets:
   - `FTP_SERVER=ftpupload.net`
   - `FTP_USERNAME=if0_xxxxxx` (xem trong InfinityFree -> FTP Details)
   - `FTP_PASSWORD` (mật khẩu hosting)
   - `FTP_SERVER_DIR=/htdocs/`
3. Mỗi `git push` lên `main` sẽ tự chạy `deploy.yml` (npm build + FTP sync). Xem log ở tab `Actions`.

**Cách B - Thủ công:**
- `npm run build` (admin) + `D:\pano> npm run deploy` (panorama) -> nén `pano-admin` (trừ `node_modules`, `vendor` nếu host đã có) -> File Manager -> Upload & Unzip đè lên `htdocs`.

**Đổi host khác:** chỉ đổi `.env` (`APP_URL`, `DB_*`) và 4 Secrets FTP. Nếu dời `pano-admin` sang folder khác, sửa `D:\pano\deploy.ps1:8` `$LaravelPublicPano` thành đường dẫn mới rồi chạy lại `npm run deploy`.

### 7. Cấu hình quan trọng
- `php artisan config:cache` sau khi đổi `.env`
- `public/build` và `vendor` đang `.gitignore` - CI sẽ build lại, không cần commit
- `SiteSetting` (`Cài đặt chung` trong `/admin`) lưu `company_name`, `logo` - Frontend tự fetch `/api/site-settings` để đổi `document.title` và favicon (`D:\pano\src\hooks\useSiteSettings.jsx`)

### 8. Tính năng chính
- TopHeader: Home/Map, Video, Image 2D/360, Pin Google Map, Help, Admin (chỉ `role=admin`), Fullscreen, ☰ ẩn/hiện toàn bộ UI
- PanoramaViewer: preload + backdrop blur chống đen, zoom 2s vào hotspot rồi sang pano đích kích thước thật, minimap + toolbar trái ẩn/hiện theo ☰
- Responsive: `responsive.css` + `100dvh`/`viewport-fit=cover`, BuildingSidebar scroll khi nhiều building

---

## 🇬🇧 English

### 1. Architecture
- **Backend:** `pano-admin` (Laravel 11 + Filament 3) - manages Project/Building/Floor/Panorama/Hotspot/Video/User/SiteSetting. API in `routes/api.php`.
- **Frontend:** `D:\pano` (React 19 + Vite 5 + Photo Sphere Viewer) - builds to `dist/` then `deploy.ps1` copies to `pano-admin/public/pano`. Served same-origin to avoid CORS.
- **Deploy:** GitHub Actions `.github/workflows/deploy.yml` runs `npm run build` and FTP deploys to `ftpupload.net` -> `/htdocs`.

### 2. Requirements
- PHP 8.2+, Composer, Node 20+, MySQL 8

### 3. Setup after clone

```bash
git clone https://github.com/phatthanh113/pano-admin.git
cd pano-admin
cp .env.example .env
# Edit .env: APP_URL, DB_*, FILESYSTEM_DISK=public
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

**React Frontend (if editing pano viewer):**
```bash
cd D:\pano
npm install
npm run deploy  # builds and copies dist -> pano-admin/public/pano
```

### 4. Demo Accounts
| ID | Password | Role |
|---|---|---|
| `admin` | `admin123` | `admin` - can access `/admin` |
| `demo` | `demo123` | `user` |

### 5. Run locally
```bash
php artisan serve # or Laragon -> http://pano-admin.test
npm run dev       # for HMR (admin)
```

### 6. Deploy to another host
**A - Auto via GitHub Actions:** Create 4 Secrets (`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_SERVER_DIR`) in repo Settings -> Secrets. Every `git push` to `main` triggers FTP sync. Check `Actions` tab.

**B - Manual:** Run `npm run build` + `npm run deploy` (in `D:\pano`), zip `pano-admin` and upload via File Manager/FTP.

**Move pano-admin folder:** Update `D:\pano\deploy.ps1:8` `$LaravelPublicPano` to new path, then re-run `npm run deploy`.

### 7. Notes
- Run `php artisan config:cache` after `.env` changes
- `public/build` and `vendor` are gitignored - CI builds them
- `SiteSetting` in `/admin` controls `company_name`/`logo` - frontend fetches `/api/site-settings` to set `document.title`/favicon

### 8. Features
- TopHeader with role-based Admin button, fullscreen, help
- PanoramaViewer with 2s zoom-to-hotspot then instant switch (preloaded + blurred backdrop), no black flash
- Fully responsive (iPhone 13 tested), BuildingSidebar scrollable

---

## License
Laravel MIT. Project specific code as per your team.
