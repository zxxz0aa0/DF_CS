# CLAUDE.md

此文件為 Claude Code (claude.ai/code) 在此代碼庫工作時提供指導。

## Claude 回答方式偏好
1. response in Traditional Chinese.
2. Before designing new features, plan ahead, discuss them thoroughly, and only execute after confirmation.
3. Focus on code security and quality
4. Create a todo list before writing or modifying code
5. When adding new code, add a brief Chinese comment at the beginning of each code segment.

# Token 使用優化規則

## 程式碼回應規則
- 只顯示**需要修改或新增的部分**，不要重複顯示整個檔案
- 如果程式碼超過 50 行，請**分段說明**，不要一次全部貼上
- 使用 `// ... 其他程式碼保持不變` 來省略不需要改動的部分
- 以穩定與可維護為優先，避免過度炫技或過度抽象。

## 回答方式
- 直接回答問題，**避免過多的開場白或總結**
- 如果我只問一個問題，請**只回答那個問題**
- 範例程式碼請**精簡到能說明概念即可**

## 檔案處理
- 讀取檔案時，如果我沒特別要求，**只讀取我詢問的功能相關程式碼**
- 當詢問時不知道或不確定程式碼在哪邊時，先提出詢問在哪個頁面裡
- 不要自動讀取整個專案的所有檔案

## 什麼時候可以詳細說明
- 我明確要求「詳細解釋」時
- 我說「我不太懂」時
- 涉及重要概念需要完整說明時

## 專案概覽

這是一個車隊管理系統（DF_CS），專為大豐交通企業開發，用於管理駕駛、車輛、帳務、文件等業務。

### 技術堆疊
- **後端**: Laravel 11.x 搭配 PHP 8.2+
- **前端**: Vue 3 (Composition API) + Inertia.js + Vite
- **樣式**: AdminLTE 4 + Bootstrap 5 + Bootstrap Icons
- **身份驗證**: Laravel Breeze 搭配 Inertia
- **權限管理**: Spatie Laravel Permission 套件 + 自訂職務權限系統
- **資料庫**: SQLite (開發環境)
- **資料表格**: DataTables.net (Vue 3 版本)
- **檔案匯出/匯入**: Maatwebsite/Excel
- **路由輔助**: Ziggy (Laravel 路由在前端使用)
- **API 認證**: Laravel Sanctum

### 開發指令

#### Laravel/PHP 指令
- **啟動開發伺服器**: `composer run dev` (並行運行所有服務：artisan serve、佇列監聽器、pail 日誌、和 vite dev)
- **執行測試**: `composer run test` 或 `php artisan test`
- **程式碼風格/檢查**: `php artisan pint` (Laravel Pint 進行程式碼格式化)
- **佇列處理器**: `php artisan queue:work`
- **資料庫遷移**: `php artisan migrate`
- **資料庫種子**: `php artisan db:seed`
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
- **Vue 3 Composition API**: 所有組件使用 Composition API
- **版面系統**:
  - `AuthenticatedLayout.vue`: 標準使用者介面
  - `AdminLayout.vue`: 管理面板介面（整合 AdminLTE 4，**必須傳入 user prop**）
  - `GuestLayout.vue`: 身份驗證頁面
- **組件庫**:
  - 位於 `resources/js/Components/` (17 個可重用組件)
  - 包含 TextInput, DateInput, Checkbox, PrimaryButton, Modal 等基礎組件
  - Vehicle/VehicleForm.vue 等模組專用組件
- **Composables 組合式函數**:
  - `usePermissions.js`: 權限檢查邏輯
  - `usePermissionLabels.js`: 權限標籤對照（含完整 77+ 權限定義）
- **頁面數量**: 87+ 個管理頁面，涵蓋所有業務功能

#### 後端結構
- **控制器**: 所有管理功能控制器位於 `app/Http/Controllers/Admin/` (19 個控制器)
  - `DashboardController`: 管理員儀表板
  - `UserManagementController`: 使用者管理
  - `RolePermissionController`: 角色與權限管理
  - `PositionController`: 職務管理
  - `CompanyCategoryController` & `CompanyController`: 公司分類與公司管理
  - `DriverController`: 駕駛管理 (含匯入/匯出/即將到期證照查詢)
  - `VehicleController`: 車輛管理 (含匯入/匯出/退籍/復籍/稽核紀錄)
  - `VehicleLicenseController`: 車輛牌照管理 (含註銷/換發)
  - `VendorController`: 廠商管理
  - `DriverVehicleAssignmentController`: 駕駛車輛綁定管理
  - `AccountMainCategoryController`: 會計總類管理
  - `AccountSubCategoryController`: 會計子分類管理 (含匯入/匯出)
  - `AccountDetailController`: 會計科目明細管理 (含匯入/匯出/自動編碼)
  - `AccountingRecordController`: 帳務記錄管理 (含批次操作)
  - `ExpensePaymentController`: 支出款項管理 (含批次狀態變更/匯入/匯出)
  - `CollectionController`: 催帳管理
  - `QuickSearchController`: 快速搜尋功能
  - `RecurringCostController`: 經常性費用管理 (含批次套用)
  - `DocumentController`: 文件管理（證件與保險管理）

- **權限控制系統**:
  - 使用 Spatie Permission 搭配中介軟體別名 (`role`, `permission`, `role_or_permission`)
  - 自訂職務(Position)權限系統: 用戶透過職務繼承權限
  - **權限優先順序**: 職務權限 > 角色權限 > 直接分配權限
  - User 模型覆寫 `hasPermissionTo()` 和 `hasAnyPermission()` 方法，優先檢查職務權限
  - 在 `HandleInertiaRequests` 中整合所有權限並分享至前端 (`$page.props.auth.permissions`)

- **模型架構**: 24 個模型，關鍵模型包含:
  - `User`: 擴展 HasRoles trait，處理職務權限優先邏輯
  - `Driver`: 軟刪除、證照到期檢查 scope、搜尋 scope
  - `Vehicle`: 軟刪除、民國年轉換、退籍/復籍方法、驗車到期檢查
  - `AccountingRecord`: 帳務記錄，關聯駕駛、車輛、會計科目
  - `RecurringCostTemplate` & `RecurringCostItem`: 經常性費用組合

- **請求驗證**: 自訂表單請求類別 (`ProfileUpdateRequest`, `Auth/LoginRequest`)

#### 資料庫結構

**完整資料表清單（34 個 Migration）**:

- **核心系統表**:
  - `users`: 使用者資料（擴展欄位，含 position_id）
  - `cache`, `cache_locks`: 快取系統
  - `jobs`, `job_batches`, `failed_jobs`: 佇列系統
  - `sessions`: 會話資料
  - `password_reset_tokens`: 密碼重置

- **權限管理表** (Spatie Permission + 自訂職務):
  - `permissions`: 權限定義
  - `roles`: 角色定義
  - `model_has_permissions`: 模型權限關聯
  - `model_has_roles`: 模型角色關聯
  - `role_has_permissions`: 角色權限關聯
  - `positions`: 職務定義
  - `position_permissions`: 職務權限關聯

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
  - 已登入且有 `view admin dashboard` 權限: 導向 `/admin/quick-search`
  - 已登入無 admin 權限: 導向 `/dashboard`

- **已認證路由**:
  - `/profile`: 個人資料管理
  - `/dashboard`: 一般使用者儀表板

- **管理員路由**: 以 `/admin` 為前綴，需要 `view admin dashboard` 權限
  - `/admin/dashboard`: 管理員儀表板
  - `/admin/quick-search`: 快速搜尋（駕駛/車輛）- **管理員登入預設頁面**
  - `/admin/users`: 使用者管理 (CRUD)
  - `/admin/roles`: 角色管理
  - `/admin/permissions`: 權限管理
  - `/admin/positions`: 職務管理 (含權限同步)
  - `/admin/company-categories` & `/admin/companies`: 公司分類與公司管理
  - `/admin/drivers`: 駕駛管理 (含匯出/匯入/即將到期證照查詢)
  - `/admin/vehicles`: 車輛管理 (含匯出/匯入/退籍/復籍/稽核紀錄/即將到期驗車查詢)
  - `/admin/vehicle-licenses`: 車輛牌照管理 (含註銷/換發)
  - `/admin/vendors`: 廠商管理
  - `/admin/driver-vehicle-assignments`: 駕駛車輛綁定管理
  - `/admin/documents`: 文件管理（證件與保險管理）
  - `/admin/accounts/main-categories`: 會計總類管理
  - `/admin/accounts/sub-categories`: 會計子分類管理 (含匯入/匯出)
  - `/admin/accounts/details`: 會計科目明細管理 (含匯入/匯出/自動編碼/代碼驗證 API)
  - `/admin/accounting/records`: 帳務記錄管理 (含批次新增/批次刪除/經常性費用套用)
  - `/admin/expense-payments`: 支出款項管理 (含批次狀態變更/匯入/匯出)
  - `/admin/collections`: 催帳管理
  - `/admin/recurring-costs`: 經常性費用管理 (含批次套用)

### AdminLTE 4 整合
- **AdminLTE CSS**: 透過 `resources/css/admin.css` 引入
- **管理版面**: `AdminLayout.vue` 提供完整的管理面板版面
  - **⚠️ 重要**: 必須傳入 `user` prop: `<AdminLayout :user="$page.props.auth.user">`，否則會導致白屏錯誤
  - 響應式側邊欄（大螢幕折疊、小螢幕抽屜）
  - 基於權限動態顯示選單項目
- **Bootstrap Icons**: 用於管理介面圖示
- **響應式設計**: 支援桌面和行動裝置
- **側邊欄選單結構**:
  - 主要功能: 快速搜尋、帳務管理、駕駛管理、車輛管理、駕駛車輛綁定、證件與保險管理、廠商管理
  - 系統設定: 會計科目管理、公司管理、使用者管理、權限管理、儀表板

### 開發流程
1. **資料庫**: 使用 `php artisan migrate` 執行遷移
2. **權限**: 使用種子檔案植入角色和權限 (`php artisan db:seed`)
   - `DatabaseSeeder` 會依序執行: RolesAndPermissionsSeeder → PositionsAndPermissionsSeeder → AdminUserSeeder → CompanyCategorySeeder
   - 各功能模組有獨立的 PermissionSeeder (共 14 個 Seeder)
3. **前端**: 透過 Vite 編譯資源，開發時使用 `composer run dev`
4. **測試**: PHPUnit 已配置，使用 `composer run test` 執行

### 需要了解的關鍵檔案
- `bootstrap/app.php`: 應用程式配置和中介軟體設定（包含 Spatie Permission 中介軟體別名註冊）
- `routes/web.php`: 所有網頁路由和中介軟體分配（**靜態路由必須放在 resource 路由之前**）
- `app/Http/Middleware/HandleInertiaRequests.php`: Inertia 共享資料（auth.user, auth.roles, auth.permissions, flash messages, ziggy）
- `app/Models/User.php`: 擴展的使用者模型，覆寫 `hasPermissionTo()` 方法實作職務權限優先邏輯
- `app/Models/Driver.php`: 駕駛模型（含證照到期檢查、軟刪除、搜尋 scope）
- `app/Models/Vehicle.php`: 車輛模型（含民國年轉換、退籍/復籍方法、軟刪除、驗車到期檢查）
- `resources/js/app.js`: 前端進入點（Inertia + Vue 3 + Ziggy 設定）
- `resources/js/Layouts/AdminLayout.vue`: AdminLTE 管理版面（**必須傳入 user prop**）
- `resources/js/Composables/usePermissions.js`: 權限檢查邏輯
- `resources/js/Composables/usePermissionLabels.js`: 完整權限標籤對照（77+ 權限）
- `resources/css/admin.css`: AdminLTE 自訂樣式（含中文字體優化、響應式調整）
- `vite.config.js`: 前端建置配置
- `database/seeders/`: 各功能模組的權限 Seeder（每個功能都有對應的 PermissionSeeder）

### 核心業務邏輯

#### 權限系統架構
- **雙軌權限機制**（三層優先順序）:
  1. **職務權限（主要）**: 用戶透過 `position_id` 關聯職務，繼承職務的所有權限
  2. **角色權限（次要）**: Spatie Permission 的角色權限系統（向後相容）
  3. **直接權限（補充）**: 可直接分配特定權限給用戶

- **實作細節**:
  - User 模型覆寫 `hasPermissionTo()` 方法，優先檢查 `hasPositionPermission()`
  - User 模型覆寫 `hasAnyPermission()` 方法，職務權限優先
  - `HandleInertiaRequests` 整合所有來源的權限並去重，分享至前端
  - 前端透過 `$page.props.auth.permissions` 存取，使用 `can()` 函數檢查

- **權限檢查**:
  - 後端: 使用 `permission:權限名稱` 中介軟體保護路由（而非 `role:admin`）
  - 前端: 使用 `can('permission-name')` 函數（定義在 usePermissions.js）

- **基礎權限清單**: 77+ 權限，涵蓋:
  - 使用者管理、角色管理、職務管理
  - 公司管理、駕駛管理、車輛管理、牌照管理
  - 會計科目管理、帳務管理、支出款項、催帳管理
  - 文件管理、廠商管理、經常性費用管理
  - 系統管理（view admin dashboard）

#### 駕駛管理
- **基本資料**: 姓名、身分證、生日、地址、聯絡資訊（住家電話、手機 x2、緊急聯絡人）
- **日期管理**: 入籍日、退籍日、車隊加入/離開日
- **證照管理**:
  - 支援普通駕照和職業駕照的到期檢查
  - 可查詢指定天數內即將到期的證照（預設 30 天）
  - 提供 `license_days_remaining` 和 `professional_license_days_remaining` accessor
  - 提供 `isLicenseExpiringSoon()` 和 `isProfessionalLicenseExpiringSoon()` 方法
- **狀態管理**: 在籍(open) / 已退籍(close)
- **關聯**: 公司分類 (`company_category_id`)、經常性費用組合 (`recurring_cost_id`)
- **匯入匯出**: 支援 Excel 格式
- **軟刪除**: 啟用軟刪除保留歷史記錄
- **搜尋功能**: 提供 `search()` scope，支援姓名、身分證、手機搜尋

#### 車輛管理
- **基本資訊**: 車牌號碼（唯一鍵）、替補車號、車主名稱
- **製造資訊**: 廠牌、出廠年月、排氣量、燃料種類、引擎號碼、車身號碼
- **車輛細節**: 載客人數、顏色、車隊名稱、車隊類別、車隊編號
- **日期系統**:
  - 支援民國年與西元年轉換
  - 使用 `convertRepublicToWestern(year, month, day)` 轉為西元日期
  - 使用 `convertWesternToRepublic(date)` 轉為民國年（回傳 [year, month, day]）
  - 資料庫分拆存儲: 發照年/月/日、檢驗年/月/日、入籍年/月/日、退籍年/月/日
- **狀態管理**:
  - 在籍(active) / 退籍(inactive)
  - 提供 `deregister()` 方法（設為 inactive 並記錄退籍日）
  - 提供 `reactivate()` 方法（設為 active 並清除退籍日）
- **牌照管理**: 支援牌照註銷、換發、替代牌照綁定
- **稽核紀錄**: 完整的車輛異動紀錄追蹤（vehicle_audit_logs）
- **驗車提醒**:
  - 支援查詢指定天數內即將到期的驗車日期
  - 提供 `inspection_days_remaining` accessor
  - 提供 `isInspectionExpiringSoon(days)` 方法
- **產權管理**: 產權類別欄位
- **關聯**: 公司分類、公司、建立者、更新者
- **匯入匯出**: 支援 Excel 格式，提供匯入範本
- **軟刪除**: 啟用軟刪除保留歷史記錄
- **搜尋功能**: 提供 `search()` scope，支援車牌號碼搜尋

#### 會計科目系統
- **三層架構**: 總類(Main) → 子分類(Sub) → 明細(Detail)
- **自動編碼**: 會計科目明細支援自動產生編碼（基於總類和子分類）
- **代碼驗證**: 提供 API 端點 `/admin/accounts/details/check-code` 驗證會計科目代碼的唯一性
- **匯入匯出**: 子分類和明細都支援 Excel 匯入匯出，提供匯入範本
- **狀態管理**: 支援啟用/停用狀態切換
- **稽核日誌**: 完整的會計科目異動紀錄（account_audit_logs）

#### 帳務管理
- **搜尋功能**: 支援多條件搜尋（日期範圍、駕駛、車輛、會計科目、公司分類等）
- **批次新增**: 一次可新增多筆帳務記錄（透過 AccountingForm.vue）
- **批次刪除**: 支援選取多筆記錄批次刪除
- **會計科目選擇**: 整合會計科目選擇器元件（AccountSelector.vue）
- **關聯查詢**:
  - 可查看駕駛和車輛的詳細資訊（透過 DriverDetailModal.vue 和 VehicleDetailModal.vue）
  - 顯示駕駛/車輛的關聯資料和歷史帳務記錄
- **經常性費用套用**: 可選擇經常性費用組合批次套用（透過 RecurringCostModal.vue）
- **頁面組件**: 包含 11 個組件，提供完整的帳務管理功能

#### 支出款項管理
- **狀態管理**: 支援批次變更狀態
- **匯入匯出**: 支援 Excel 匯入匯出，提供匯入範本
- **資料列表**: 使用 DataTables.net 展示資料

#### 經常性費用
- **組合範本**: 將多個費用項目組合成範本（RecurringCostTemplate）
- **費用項目**: 每個範本可包含多個費用項目（RecurringCostItem），每項都對應一個會計科目
- **批次套用**:
  - 可將經常性費用組合一次套用到帳務記錄（自動產生多筆帳務記錄）
  - 透過 RecurringCostModal.vue 選擇組合並指定套用參數
- **駕駛綁定**: 駕駛可以綁定經常性費用組合（drivers 表的 recurring_cost_id）

#### 催帳管理
- **公司分類搜尋**: 支援依公司分類查詢
- **單頁應用**: 目前僅包含 Index.vue（可能是單頁完整功能）

#### 快速搜尋
- **整合搜尋**: 可同時搜尋駕駛和車輛資訊
- **詳細資訊**:
  - 透過 DriverDetailModal.vue 顯示駕駛的完整資訊（包含關聯的車輛、帳務記錄等）
  - 透過 VehicleDetailModal.vue 顯示車輛的完整資訊（包含關聯的駕駛、帳務記錄等）
- **管理員首頁**: 有 `view admin dashboard` 權限的用戶登入後預設導向快速搜尋頁面

#### 文件管理
- **實際名稱**: 證件與保險管理
- **檔案管理**: 支援多檔案上傳（document_files 表）
- **權限控制**: 需要 `view documents` 權限

## 匯入匯出功能

### 已實作的匯出功能
- **駕駛管理**: 駕駛資料匯出
- **車輛管理**: 車輛資料匯出、車輛匯入範本匯出
- **會計子分類**: 子分類資料匯出
- **會計科目明細**: 科目明細資料匯出
- **支出款項**: 支出款項資料匯出、支出款項範本匯出

### 已實作的匯入功能
- **車輛管理**: 車輛資料匯入（使用 VehiclesImport 類別）
- **支出款項**: 支出款項匯入（使用 ExpensePaymentsImport 類別）

### 使用的 Export/Import 類別
- `app/Exports/VehiclesExport.php`
- `app/Exports/VehicleTemplateExport.php`
- `app/Exports/ExpensePaymentsExport.php`
- `app/Exports/ExpensePaymentsTemplateExport.php`
- `app/Imports/VehiclesImport.php`
- `app/Imports/ExpensePaymentsImport.php`

## 安全性與最佳實踐

### 安全性考量
- **權限驗證**: 所有管理路由都使用 `permission` 中介軟體進行權限檢查
- **自我保護**: 使用者無法刪除自己的帳號
- **密碼安全**: 使用 Laravel 的預設 bcrypt 雜湊機制
- **CSRF 保護**: 已啟用 Laravel 預設的 CSRF 保護（透過 Inertia.js 自動處理）
- **軟刪除**: 重要資料（駕駛、車輛）使用軟刪除保留歷史記錄
- **稽核追蹤**: 車輛和會計科目異動都有完整的稽核日誌
- **XSS 防護**: Vue 3 預設轉義輸出，避免 XSS 攻擊
- **SQL 注入防護**: 使用 Eloquent ORM 和參數綁定

### 路由定義規則
- **⚠️ 靜態路由優先**: 在 `routes/web.php` 中，靜態路由（如 `export`, `import`, `template`）**必須**定義在 `Route::resource()` 之前
  ```php
  // ✅ 正確順序
  Route::get('drivers/export', [DriverController::class, 'export'])->name('drivers.export');
  Route::get('drivers/template', [DriverController::class, 'template'])->name('drivers.template');
  Route::resource('drivers', DriverController::class);

  // ❌ 錯誤順序（靜態路由會被 resource 的 show 路由攔截，導致 404）
  Route::resource('drivers', DriverController::class);
  Route::get('drivers/export', [DriverController::class, 'export']); // 無效！
  ```
- **權限中介軟體**: 使用 `permission:權限名稱` 而非 `role:角色名稱`，以支援職務權限系統
- **中介軟體群組**: 管理路由使用 `middleware: ['auth', 'verified', 'permission:view admin dashboard']`

### Inertia.js 最佳實踐
- **共享資料**: 所有頁面都可透過 `$page.props` 存取共享資料（auth, flash, ziggy）
- **路由輔助**: 使用 Ziggy 的 `route()` 函數產生路由 URL（前端與後端路由名稱一致）
- **Flash 訊息**: 使用 `session()->flash('success|error|warning', '訊息')` 傳遞訊息至前端
- **表單提交**: 使用 Inertia 的 `router.post()`, `router.put()`, `router.delete()` 方法
- **保留滾動位置**: 必要時使用 `preserveScroll: true` 選項

### Vue 3 最佳實踐
- **Composition API**: 所有組件使用 Composition API（`<script setup>`）
- **Composables**: 重用邏輯抽取到 Composables（如 usePermissions.js）
- **Props 驗證**: 定義 Props 時加入型別和必填驗證
- **事件命名**: 使用 kebab-case（如 `@update-status`）

## 常見問題與解決方案

### ⚠️ AdminLayout 使用注意事項
- **重要**: 所有使用 `AdminLayout` 的 Vue 組件**必須傳入 `user` prop**，否則會導致白屏錯誤
- **正確用法**:
  ```vue
  <template>
    <AdminLayout :user="$page.props.auth.user">
      <!-- 頁面內容 -->
    </AdminLayout>
  </template>
  ```
- **錯誤原因**: AdminLayout 內部需要 `user.name` 來顯示使用者名稱，缺少此 prop 會造成 Vue 渲染錯誤
- **修復方式**: 在所有管理頁面的 template 標籤中確保正確傳入 user prop

### Bootstrap Modal 初始化問題
- **問題**: 在 Vue 組件中直接初始化 Bootstrap Modal 可能因為 DOM 時機問題導致白屏
- **解決方案**: 使用 `setTimeout` 延遲初始化
  ```javascript
  import { onMounted } from 'vue'

  let deleteModal = null

  onMounted(() => {
      setTimeout(() => {
          const modalElement = document.getElementById('deleteModal')
          if (modalElement && window.bootstrap) {
              deleteModal = new window.bootstrap.Modal(modalElement)
          }
      }, 100)
  })
  ```
- **原因**: Vue 的 DOM 更新是非同步的，直接在 `onMounted` 中初始化可能取不到 DOM 元素

### 新增功能模組的步驟
1. **建立 Migration**: `php artisan make:migration create_xxx_table`
2. **建立 Model**: `php artisan make:model XxxModel`，根據需求加入關聯、scope、accessor 等
3. **建立 Controller**: `php artisan make:controller Admin/XxxController`
4. **新增路由**: 在 `routes/web.php` 的 `/admin` 群組中新增路由（**注意靜態路由順序**）
5. **建立權限 Seeder**: `php artisan make:seeder XxxPermissionSeeder`，定義該模組的權限
6. **執行 Migration 和 Seeder**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=XxxPermissionSeeder
   ```
7. **建立 Vue 頁面**: 在 `resources/js/Pages/Admin/Xxx/` 建立相關頁面（Index.vue, Create.vue, Edit.vue, Show.vue）
8. **更新職務權限**: 在管理介面（/admin/positions）將新權限分配給對應職務
9. **測試**: 使用不同權限的帳號測試功能存取控制

### DataTables 整合注意事項
- **套件**: 使用 `datatables.net-vue3` 套件
- **CSS**: 使用 Bootstrap 5 版本: `datatables.net-bs5`
- **使用方式**:
  ```javascript
  import DataTable from 'datatables.net-vue3'
  import DataTablesCore from 'datatables.net-bs5'

  DataTable.use(DataTablesCore)
  ```
- **中文化**: 在 options 中設定 `language.url` 或直接設定中文標籤
- **響應式**: 使用 `datatables.net-responsive-bs5` 套件支援行動裝置

### Excel 匯入匯出
- **套件**: 使用 `Maatwebsite\Excel` 套件
- **匯出**:
  - 建立 Export 類別繼承 `FromCollection` 或 `FromQuery`
  - 實作 `collection()` 或 `query()` 方法
  - 可實作 `headings()` 方法定義標題列
- **匯入**:
  - 建立 Import 類別繼承 `ToModel` 或 `ToCollection`
  - 實作 `model(array $row)` 或 `collection(Collection $rows)` 方法
  - 可加入驗證邏輯和錯誤處理
- **模板下載**: 提供空白或範例資料的 Excel 檔案供用戶下載
- **使用範例**:
  ```php
  // 匯出
  return Excel::download(new VehiclesExport, 'vehicles.xlsx');

  // 匯入
  Excel::import(new VehiclesImport, $request->file('file'));
  ```

### 民國年轉換
- **轉換方法**: 使用 Vehicle 模型的靜態方法
  ```php
  // 民國年轉西元
  $westernDate = Vehicle::convertRepublicToWestern(113, 12, 13); // 2024-12-13

  // 西元轉民國年
  [$year, $month, $day] = Vehicle::convertWesternToRepublic('2024-12-13'); // [113, 12, 13]
  ```
- **資料庫存儲**: 使用年、月、日分拆欄位（如 `issue_year`, `issue_month`, `issue_day`）
- **前端顯示**: 透過 accessor 自動轉換（如 `inspection_date` accessor）

## 測試

### 測試架構
- **測試框架**: PHPUnit 11.x
- **執行指令**: `composer run test` 或 `php artisan test`
- **測試類型**: Feature 測試（認證相關）、Unit 測試
- **測試資料庫**: 使用 SQLite in-memory 資料庫（`:memory:`）

### 現有測試
- **認證測試** (9 個 Feature 測試):
  - 登入畫面測試
  - 登入功能測試
  - 登出功能測試
  - 註冊功能測試
  - 密碼重置測試
  - 郵件驗證測試
  - 個人資料更新測試

### 測試建議
- 為關鍵業務邏輯添加測試（駕駛管理、車輛管理、帳務管理）
- 測試權限系統（職務權限優先邏輯）
- 測試匯入匯出功能（驗證資料正確性）
- 測試民國年轉換邏輯
- 測試軟刪除和稽核日誌功能

## 專案統計

- **後端控制器**: 30 個（10 個認證 + 19 個管理 + 1 個個人資料）
- **前端頁面**: 87+ 個管理頁面
- **Vue 組件**: 17 個可重用組件
- **資料表**: 34 個 Migration
- **模型**: 24 個 Eloquent 模型
- **權限**: 77+ 個權限定義
- **Seeder**: 14 個資料庫種子檔案
- **總程式碼量**: 控制器約 4,529 行，權限標籤約 6,574 行

## 未來改進建議

1. **測試覆蓋率**: 補充業務邏輯的單元測試和整合測試
2. **API 文件**: 使用 Scribe 或 L5-Swagger 生成 API 文件
3. **前端優化**: 提取共用的 Modal 組件，統一 DataTables 配置
4. **效能優化**: 添加快取機制、資料庫索引優化
5. **監控與日誌**: 整合 Laravel Telescope 用於開發環境除錯
6. **部署**: 建立 CI/CD 流程，使用 Laravel Forge 或 Envoyer 部署

---

**文件版本**: 2.0
**最後更新**: 2025-12-13
**專案版本**: 1.0.0
