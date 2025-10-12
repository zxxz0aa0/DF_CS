# CLAUDE.md

此文件為 Claude Code (claude.ai/code) 在此代碼庫工作時提供指導。

## 個人偏好
1. 使用繁體中文應答
2. 設計新功能前先規劃，並討論完後，有確定再執行
3. 
4. 注重安全性問題
5. 開始寫程式或修改程式前先做一個to do
6. 以專業的角度看待所有開發規劃或內容，若有問題不完整給予建議或參考方案

## 專案概覽

這是一個車隊管理系統,用於管理駕駛、車輛、帳務、文件等業務。

### 技術堆疊
- **後端**: Laravel 11.x 搭配 PHP 8.4+
- **前端**: Vue 3 (Composition API) + Inertia.js + Vite
- **樣式**: AdminLTE 4 + Bootstrap 5 + Bootstrap Icons
- **身份驗證**: Laravel Breeze 搭配 Inertia
- **權限管理**: Spatie Laravel Permission 套件 + 自訂職務權限系統
- **資料庫**: SQLite (開發環境)
- **資料表格**: DataTables.net (Vue 3 版本)
- **檔案匯出/匯入**: Maatwebsite/Excel
- **路由輔助**: Ziggy (Laravel 路由在前端使用)

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
- **控制器**: 所有管理功能控制器位於 `app/Http/Controllers/Admin/`
  - `DashboardController`: 管理員儀表板
  - `UserManagementController`: 使用者管理
  - `RolePermissionController`: 角色與權限管理
  - `PositionController`: 職務管理
  - `DriverController`: 駕駛管理 (含匯入/匯出)
  - `VehicleController`: 車輛管理 (含匯入/匯出/退籍/復籍)
  - `VehicleLicenseController`: 車輛牌照管理 (含註銷/換發)
  - `CompanyCategoryController` & `CompanyController`: 公司分類與公司管理
  - `AccountMainCategoryController`, `AccountSubCategoryController`, `AccountDetailController`: 會計科目三層管理
  - `AccountingRecordController`: 帳務記錄管理
  - `ExpensePaymentController`: 支出款項管理
  - `CollectionController`: 催帳管理
  - `QuickSearchController`: 快速搜尋功能
  - `RecurringCostController`: 經常性費用管理
  - `DocumentController`: 文件管理
  - `DriverVehicleAssignmentController`: 駕駛車輛綁定管理
  - `VendorController`: 廠商管理
- **權限控制系統**:
  - 使用 Spatie Permission 搭配中介軟體別名 (`role`, `permission`, `role_or_permission`)
  - 自訂職務(Position)權限系統: 用戶透過職務繼承權限
  - 權限優先順序: 職務權限 > 角色權限 > 直接分配權限
  - 在 `HandleInertiaRequests` 中整合所有權限並分享至前端
- **請求驗證**: 自訂表單請求類別 (`ProfileUpdateRequest`, `Auth/LoginRequest`)

#### 資料庫結構
- **用戶與權限**:
  - `users`: 擴展的使用者表，包含職務(position_id)關聯
  - Spatie Permission 套件表: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`
  - `positions`: 職務表
  - `position_permissions`: 職務權限關聯表
- **公司管理**:
  - `company_categories`: 公司分類
  - `companies`: 公司資料
- **駕駛管理**:
  - `drivers`: 駕駛資料 (含軟刪除)
  - `driver_balance_summary`: 駕駛餘額彙總
- **車輛管理**:
  - `vehicles`: 車輛資料 (含軟刪除)
  - `vehicle_licenses`: 車輛牌照
  - `vehicle_license_audit_logs`: 牌照異動紀錄
  - `vehicle_audit_logs`: 車輛異動紀錄
  - `vehicle_config_settings`: 車輛設定
- **駕駛車輛綁定**:
  - `driver_vehicle_assignments`: 駕駛與車輛的多對多關聯
- **會計管理**:
  - `account_main_categories`: 會計總類
  - `account_sub_categories`: 會計子分類
  - `account_details`: 會計科目明細
  - `account_audit_logs`: 會計科目異動紀錄
  - `accounting_records`: 帳務記錄
  - `expense_payments`: 支出款項
- **經常性費用**:
  - `recurring_cost_templates`: 經常性費用組合範本
  - `recurring_cost_items`: 經常性費用項目
- **文件管理**:
  - `documents`: 文件資料
  - `document_files`: 文件檔案
- **其他**:
  - `vendors`: 廠商資料

#### 路由結構
- **公開路由**: 根路徑 `/` 會根據登入狀態和權限自動導向
  - 未登入: 導向 `/login`
  - 已登入且有 admin 權限: 導向 `/admin/quick-search`
  - 已登入無 admin 權限: 導向 `/dashboard`
- **已認證路由**: 個人資料管理 (`/profile`)
- **管理員路由**: 以 `/admin` 為前綴，需要 `view admin dashboard` 權限
  - `/admin/dashboard`: 管理員儀表板
  - `/admin/users`: 使用者管理 (CRUD)
  - `/admin/roles`: 角色管理
  - `/admin/permissions`: 權限管理
  - `/admin/positions`: 職務管理 (含權限同步)
  - `/admin/company-categories` & `/admin/companies`: 公司分類與公司管理
  - `/admin/drivers`: 駕駛管理 (含匯出/匯入/即將到期證照查詢)
  - `/admin/vehicles`: 車輛管理 (含匯出/匯入/退籍/復籍/稽核紀錄)
  - `/admin/vehicle-licenses`: 車輛牌照管理 (含註銷/換發)
  - `/admin/vendors`: 廠商管理
  - `/admin/driver-vehicle-assignments`: 駕駛車輛綁定管理
  - `/admin/documents`: 文件管理 (需 `view documents` 權限)
  - `/admin/accounts/main-categories`: 會計總類管理
  - `/admin/accounts/sub-categories`: 會計子分類管理 (含匯入/匯出)
  - `/admin/accounts/details`: 會計科目明細管理 (含匯入/匯出/自動編碼)
  - `/admin/accounting/records`: 帳務記錄管理 (含批次操作)
  - `/admin/expense-payments`: 支出款項管理 (含批次狀態變更/匯入/匯出)
  - `/admin/collections`: 催帳管理 (含公司總類搜尋)
  - `/admin/quick-search`: 快速搜尋 (駕駛/車輛)
  - `/admin/recurring-costs`: 經常性費用管理 (含批次套用)

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
- `bootstrap/app.php`: 應用程式配置和中介軟體設定（包含 Spatie Permission 中介軟體別名註冊）
- `routes/web.php`: 所有網頁路由和中介軟體分配（靜態路由必須放在 resource 路由之前）
- `app/Http/Middleware/HandleInertiaRequests.php`: Inertia 共享資料（auth.user, auth.roles, auth.permissions, flash messages, ziggy）
- `app/Models/User.php`: 擴展的使用者模型包含角色/權限和職務關聯
- `app/Models/Driver.php`: 駕駛模型（含證照到期檢查、軟刪除、搜尋 scope）
- `app/Models/Vehicle.php`: 車輛模型（含民國年轉換、退籍/復籍方法、軟刪除）
- `resources/js/app.js`: 前端進入點（Inertia + Vue 3 + Ziggy 設定）
- `resources/js/Layouts/AdminLayout.vue`: AdminLTE 管理版面（必須傳入 user prop）
- `resources/css/admin.css`: AdminLTE 自訂樣式
- `vite.config.js`: 前端建置配置
- `database/seeders/`: 各功能模組的權限 Seeder（每個功能都有對應的 PermissionSeeder）

### 核心業務邏輯

#### 權限系統架構
- **雙軌權限機制**:
  1. **職務權限（主要）**: 用戶透過 `position_id` 關聯職務，繼承職務的所有權限
  2. **角色權限（次要）**: Spatie Permission 的角色權限系統（向後相容）
  3. **直接權限（補充）**: 可直接分配特定權限給用戶
- **權限檢查**: 使用 `permission:權限名稱` 中介軟體保護路由（而非 `role:admin`）
- **前端權限**: 透過 `HandleInertiaRequests` 整合所有權限並分享至 `$page.props.auth.permissions`

#### 駕駛管理
- **證照到期提醒**:
  - 支援普通駕照和職業駕照的到期檢查
  - 可查詢指定天數內即將到期的證照（預設 30 天）
- **狀態管理**: 在籍(open) / 已退籍(close)
- **匯入匯出**: 支援 Excel 格式
- **軟刪除**: 啟用軟刪除保留歷史記錄

#### 車輛管理
- **日期系統**: 支援民國年與西元年轉換（使用 `convertRepublicToWestern` 和 `convertWesternToRepublic` 方法）
- **車輛狀態**: 在籍(active) / 退籍(inactive)
- **退籍/復籍**: 提供 `deregister()` 和 `reactivate()` 方法
- **牌照管理**: 支援牌照註銷、換發、替代牌照綁定
- **稽核紀錄**: 完整的車輛異動紀錄追蹤
- **驗車提醒**: 支援查詢指定天數內即將到期的驗車日期

#### 會計科目系統
- **三層架構**: 總類(Main) → 子分類(Sub) → 明細(Detail)
- **自動編碼**: 會計科目明細支援自動產生編碼（基於總類和子分類）
- **代碼驗證**: 提供 API 端點驗證會計科目代碼的唯一性
- **匯入匯出**: 子分類和明細都支援 Excel 匯入匯出
- **狀態管理**: 支援啟用/停用狀態切換

#### 帳務管理
- **搜尋功能**: 支援多條件搜尋（日期、駕駛、車輛、會計科目等）
- **批次新增**: 一次可新增多筆帳務記錄
- **批次刪除**: 支援選取多筆記錄批次刪除
- **會計科目選擇**: 整合會計科目選擇器元件（AccountSelector）
- **關聯查詢**: 可查看駕駛和車輛的詳細資訊（透過 Modal）

#### 經常性費用
- **組合範本**: 將多個費用項目組合成範本（RecurringCostTemplate）
- **費用項目**: 每個範本可包含多個費用項目（RecurringCostItem），每項都對應一個會計科目
- **批次套用**: 可將經常性費用組合一次套用到帳務記錄（自動產生多筆帳務記錄）
- **駕駛綁定**: 駕駛可以綁定經常性費用組合

#### 快速搜尋
- **整合搜尋**: 可同時搜尋駕駛和車輛資訊
- **詳細資訊**: 透過 Modal 顯示駕駛/車輛的完整資訊（包含關聯的車輛/駕駛、帳務記錄等）
- **管理員首頁**: 有 admin 權限的用戶登入後預設導向快速搜尋頁面

## 安全性與最佳實踐

### 安全性考量
- **權限驗證**: 所有管理路由都使用 `permission` 中介軟體進行權限檢查
- **自我保護**: 使用者無法刪除自己的帳號
- **密碼安全**: 使用 Laravel 的預設 bcrypt 雜湊機制
- **CSRF 保護**: 已啟用 Laravel 預設的 CSRF 保護
- **軟刪除**: 重要資料（駕駛、車輛）使用軟刪除保留歷史記錄
- **稽核追蹤**: 車輛和會計科目異動都有完整的稽核日誌

### 路由定義規則
- **靜態路由優先**: 在 `routes/web.php` 中，靜態路由（如 `export`, `import`, `template`）必須定義在 `Route::resource()` 之前
  ```php
  // 正確順序
  Route::get('drivers/export', [DriverController::class, 'export'])->name('drivers.export');
  Route::resource('drivers', DriverController::class);

  // 錯誤順序（靜態路由會被 resource 的 show 路由攔截）
  Route::resource('drivers', DriverController::class);
  Route::get('drivers/export', [DriverController::class, 'export'])->name('drivers.export'); // 無效！
  ```
- **權限中介軟體**: 使用 `permission:權限名稱` 而非 `role:角色名稱`，以支援職務權限系統

### Inertia.js 最佳實踐
- **共享資料**: 所有頁面都可透過 `$page.props` 存取共享資料（auth, flash, ziggy）
- **路由輔助**: 使用 Ziggy 的 `route()` 函數產生路由 URL（前端與後端路由名稱一致）
- **Flash 訊息**: 使用 `session()->flash('success|error|warning', '訊息')` 傳遞訊息至前端

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

### 新增功能模組的步驟
1. **建立 Migration**: `php artisan make:migration create_xxx_table`
2. **建立 Model**: `php artisan make:model XxxModel`，根據需求加入關聯、scope、accessor 等
3. **建立 Controller**: `php artisan make:controller Admin/XxxController`
4. **新增路由**: 在 `routes/web.php` 的 `/admin` 群組中新增路由（注意靜態路由順序）
5. **建立權限 Seeder**: `php artisan make:seeder XxxPermissionSeeder`，定義該模組的權限
6. **執行 Migration 和 Seeder**: `php artisan migrate && php artisan db:seed --class=XxxPermissionSeeder`
7. **建立 Vue 頁面**: 在 `resources/js/Pages/Admin/Xxx/` 建立相關頁面
8. **更新職務權限**: 在管理介面將新權限分配給對應職務

### DataTables 整合注意事項
- 使用 `datatables.net-vue3` 套件
- CSS 使用 Bootstrap 5 版本: `datatables.net-bs5`
- 在 Vue 組件中引入:
  ```javascript
  import DataTable from 'datatables.net-vue3'
  import DataTablesCore from 'datatables.net-bs5'
  DataTable.use(DataTablesCore)
  ```

### Excel 匯入匯出
- 使用 `Maatwebsite\Excel` 套件
- 匯出: 建立 Export 類別繼承 `FromCollection` 或 `FromQuery`
- 匯入: 建立 Import 類別繼承 `ToModel` 或 `ToCollection`
- 模板下載: 提供空白或範例資料的 Excel 檔案供用戶下載
