# CLAUDE.md

此文件為 Claude Code (claude.ai/code) 在此代碼庫工作時提供指導。

## 個人偏好
1. 使用繁體中文應答
2. 設計新功能前先規劃，並討論完後，有確定再執行
3. 做任何分析、查詢、檢查時使用Claude Opus 4模型，編寫代碼時使用Claude Sonnet 4模型，來降低使用次數成本。
4. 注重安全性問題
5. 開始寫程式或修改程式前先做一個to do

## 專案概覽

### 技術堆疊
- **後端**: Laravel 11.x 搭配 PHP 8.4+
- **前端**: Vue 3 + Inertia.js + Vite
- **樣式**: AdminLTE 4 + Bootstrap 5 + Bootstrap Icons
- **身份驗證**: Laravel Breeze 搭配 Inertia
- **權限管理**: Spatie Laravel Permission 套件
- **資料庫**: SQLite (開發環境)

### 開發指令

#### Laravel/PHP 指令
- **啟動開發伺服器**: `composer run dev` (並行運行所有服務：artisan serve、佇列監聽器、pail 日誌、和 vite dev)
- **執行測試**: `composer run test` 或 `php artisan test`
- **程式碼風格/檢查**: `php artisan pint` (Laravel Pint 進行程式碼格式化)
- **佇列處理器**: `php artisan queue:work`
- **資料庫遷移**: `php artisan migrate`
- **清除配置快取**: `php artisan config:clear`
- **Tinker REPL**: `php artisan tinker`

#### 前端指令  
- **前端開發伺服器**: `npm run dev` (Vite 開發伺服器)
- **生產環境建置**: `npm run build`
- **僅啟動 Laravel**: `npm run serve` 或 `php artisan serve`
- **同時運行兩個服務**: `npm run dev:all` (並行 PHP 伺服器 + Vite)

## 架構概覽

### 關鍵架構模式

#### 前端結構
- **Inertia.js**: 伺服器端路由搭配客戶端導航
- **Vue 3 Composition API**: 組件中使用
- **版面系統**: 
  - `AuthenticatedLayout.vue`: 標準使用者介面
  - `AdminLayout.vue`: 管理面板介面（整合 AdminLTE 4）
  - `GuestLayout.vue`: 身份驗證頁面
- **組件庫**: 可重用的 Vue 組件位於 `/resources/js/Components/`

#### 後端結構
- **控制器**: 
  - Auth 控制器處理身份驗證流程
  - `ProfileController`: 使用者資料管理
  - `Admin/DashboardController`: 管理員儀表板
  - `Admin/UserManagementController`: 管理員使用者管理
- **角色型存取控制**: 使用 Spatie Permission 搭配中介軟體別名 (`role`, `permission`, `role_or_permission`)
- **請求驗證**: 自訂表單請求類別 (`ProfileUpdateRequest`, `Auth/LoginRequest`)

#### 資料庫結構
- 擴展的使用者模型包含 Spatie Permission traits
- 透過 Spatie 套件提供角色和權限表
- 標準 Laravel 身份驗證表

#### 路由結構
- **公開路由**: 歡迎頁面
- **已認證路由**: 儀表板、個人資料管理
- **管理員路由**: 以 `/admin` 為前綴，需要管理員角色
  - 管理員儀表板位於 `/admin/dashboard`
  - 使用者管理位於 `/admin/users`
  - 角色分配功能

### AdminLTE 4 整合
- **AdminLTE CSS**: 透過 `resources/css/admin.css` 引入
- **管理版面**: `AdminLayout.vue` 提供完整的管理面板版面
- **Bootstrap Icons**: 用於管理介面圖示
- **響應式設計**: 支援桌面和行動裝置

### 開發流程
1. **資料庫**: 使用 `php artisan migrate` 執行遷移
2. **權限**: 使用種子檔案植入角色和權限
3. **前端**: 透過 Vite 編譯資源，開發時使用 `composer run dev`
4. **測試**: PHPUnit 已配置，使用 `composer run test` 執行

### 需要了解的關鍵檔案
- `bootstrap/app.php`: 應用程式配置和中介軟體設定
- `routes/web.php`: 所有網頁路由和中介軟體分配
- `app/Models/User.php`: 擴展的使用者模型包含角色/權限
- `resources/js/app.js`: 前端進入點
- `resources/js/Layouts/AdminLayout.vue`: AdminLTE 管理版面
- `resources/css/admin.css`: AdminLTE 自訂樣式
- `vite.config.js`: 前端建置配置

### 角色與權限系統
- **管理員角色**: `admin` 角色可存取管理面板
- **權限檢查**: 使用 `role:admin` 中介軟體保護管理路由
- **使用者管理**: 管理員可建立、編輯、刪除其他使用者
- **角色分配**: 管理員可為使用者分配角色

### 管理面板功能
- **儀表板**: 顯示系統統計和快速操作
- **使用者管理**: 完整的 CRUD 操作
- **搜尋與分頁**: 支援使用者搜尋和分頁顯示
- **響應式介面**: 適應各種螢幕尺寸

## 安全性考量
- 所有管理路由都有適當的權限檢查
- 使用者無法刪除自己的帳號
- 密碼使用 Laravel 的預設雜湊機制
- CSRF 保護已啟用

## 常見問題與解決方案

### AdminLayout 使用注意事項
- **重要**: 所有使用 `AdminLayout` 的 Vue 組件必須傳入 `user` prop，否則會導致白屏錯誤
- **正確用法**: `<AdminLayout :user="$page.props.auth.user">`
- **錯誤原因**: AdminLayout 內部需要 `user.name` 來顯示使用者名稱，缺少此 prop 會造成 Vue 渲染錯誤
- **修復方式**: 在所有管理頁面的 template 標籤中確保正確傳入 user prop

### Bootstrap Modal 初始化問題
- **問題**: 在 Vue 組件中直接初始化 Bootstrap Modal 可能因為 DOM 時機問題導致白屏
- **解決方案**: 使用 `setTimeout` 延遲初始化
```javascript
onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('deleteModal')
        if (modalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(modalElement)
        }
    }, 100)
})
```

# important-instruction-reminders
Do what has been asked; nothing more, nothing less.
NEVER create files unless they're absolutely necessary for achieving your goal.
ALWAYS prefer editing an existing file to creating a new one.
NEVER proactively create documentation files (*.md) or README files. Only create documentation files if explicitly requested by the User.