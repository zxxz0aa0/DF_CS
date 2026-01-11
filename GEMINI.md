# 系統架構分析：DF_CS (大豐車隊管理系統)

## 1. 系統總覽
**DF_CS** 是一個專為管理計程車隊營運而設計的單體式 Web 應用程式，涵蓋駕駛、車輛、帳務及文件管理。系統基於 **Laravel 11** 和 **Vue 3** 建構，並使用 **Inertia.js** 實現現代化的單頁應用 (SPA) 體驗，同時避免了分離式 API 的複雜性。

## 2. 技術堆疊

### 後端 (Backend)
- **框架**: Laravel 11.x (PHP 8.2+)
- **資料庫**: SQLite (開發用) / MySQL (預計生產用)
- **ORM**: Eloquent
- **身份驗證**: Laravel Breeze + Sanctum (用於 API)
- **權限管理**: Spatie Laravel Permission (經過客製化)
- **Excel 處理**: Maatwebsite/Excel

### 前端 (Frontend)
- **框架**: Vue 3 (Composition API)
- **橋接**: Inertia.js (伺服器驅動路由)
- **建置工具**: Vite
- **UI 框架**: 
    - **AdminLTE 4** (版面配置與核心樣式)
    - **Bootstrap 5** (組件)
    - **Tailwind CSS** (通用工具類)
- **表格**: DataTables.net (Vue 3 版本)

## 3. 核心架構模式

### 3.1 Inertia.js 單體架構 (Modern Monolith)
本應用程式採用「現代單體」模式。
- **路由**: 定義於 [routes/web.php](file:///Users/raystyle/Desktop/Ray-Project/DF_CS/routes/web.php)。前端不需要獨立的 REST API。
- **控制器**: 回傳 `Inertia::render('PageName', $props)` 而非 JSON。
- **狀態**: 頁面屬性 (Props) 直接從 Laravel 傳遞給 Vue。
- **共享資料**: 透過 `HandleInertiaRequests` 中介軟體將 `auth.user` 和 `permissions` 等全域資料分享給每個頁面。

### 3.2 混合權限系統
系統實作了複雜的多層次權限策略：
1.  **職務導向 (主要)**: 使用者被分配一個「職務」(Position)（例如：會計、經理）。
2.  **角色導向 (次要)**: 底層使用 Spatie Roles。
3.  **直接權限 (補充)**: 針對個人的細粒度覆寫。
4.  **實作**: `User` 模型覆寫了 `hasPermissionTo` 方法，優先檢查職務權限。

### 3.3 領域驅動模組
應用程式依據核心業務領域進行結構化，這反映在模型和控制器中：

#### A. 駕駛管理 (Driver Management)
- **模型**: `Driver`, `DriverBalanceSummary`, `DriverVehicleAssignment`
- **關鍵邏輯**: 證照到期追蹤、餘額計算、經常性費用關聯。
- **軟刪除**: 已啟用以保留歷史資料。

#### B. 車輛管理 (Vehicle Management)
- **模型**: `Vehicle`, `VehicleLicense`, `VehicleAuditLog`
- **關鍵邏輯**: 
    - **民國年轉換**: 處理民國年（如 113 年）至西元的轉換邏輯。
    - **狀態機**: 在籍 (Active) vs 已退籍 (Inactive)。
    - **稽核紀錄**: 追蹤所有敏感車輛資料的變更。

#### C. 會計系統 (Accounting System)
- **結構**: 三層式階層
    1.  `AccountMainCategory` (總類)
    2.  `AccountSubCategory` (子分類)
    3.  `AccountDetail` (明細)
- **功能**: 自動編碼生成、Excel 匯入/匯出、稽核日誌。

#### D. 經常性費用 (Recurring Costs)
- **模型**: `RecurringCostTemplate`, `RecurringCostItem`
- **邏輯**: 基於範本的實作，可將標準化的月費批次套用到駕駛/車輛。

## 4. 目錄結構重點

```
/
├── app/
│   ├── Http/Controllers/Admin/  # 所有業務邏輯控制器
│   ├── Models/                  # 豐富的領域模型 (24+ 個模型)
│   ├── Exports/ & Imports/      # Excel 處理邏輯
│   └── Policies/                # 授權邏輯
├── resources/
│   ├── js/
│   │   ├── Pages/Admin/         # Vue 頁面組件 (87+ 頁面)
│   │   ├── Components/          # 共用 UI 組件 (Modals, Inputs)
│   │   └── Composables/         # 共用邏輯 (usePermissions.js)
│   └── css/                     # AdminLTE & Tailwind 覆寫
└── 開發文件/                     # 詳細的功能規格書
```

## 5. 關鍵設計決策
1.  **伺服器端路由**: 使用 Ziggy 將 Laravel 命名路由暴露給 Vue 前端，保持緊密耦合與型別安全。
2.  **DTO 風格請求**: 使用自訂的 FormRequest 類別 (`ProfileUpdateRequest`) 處理驗證，保持控制器整潔。
3.  **稽核軌跡**: 關鍵模組（車輛、會計）擁有專用的 `_audit_logs` 資料表，顯示對問責性的高度需求。
4.  **Excel 整合**: 高度依賴 Excel 進行資料遷移和報表製作，並由專門的 Export/Import 類別支援。

## AI Behavior & Constraints
1. **實事求是**：已查明的問題請直接回答，未查明的問題請提出假設或詢問，不要幻想 API 或私自引入未安裝的套件。
2. **禁止炫技**：代碼以「易讀、好維護」為最高原則，避免過度工程化或使用晦澀語法。
3. **尊重現狀**：未經允許不得擅自刪除或重寫與任務無關的現有邏輯。
4. **最小改動**：修改時應專注於解決問題，並保持專案原有的縮排與命名風格。
5. **說明邏輯**：不需要過多說明，簡單易懂的說明即可，不要長篇大論或一段話有很多重複說明
6. **明確區分責任**：一個 function 只做一件事、一個 class 只負責一個角色、Controller 不處理複雜商業邏輯商業邏輯集中在 Service
7. **使用者語言偏好**：請使用中文與我溝通
