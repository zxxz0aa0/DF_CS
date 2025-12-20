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

# DF_CS 專案 - Laravel ERP 系統

## 技術棧
Laravel 10 + Vue 3 + Bootstrap 5 + MySQL 8

## 快速指令
```bash
# 開發環境 (Laravel Sail/Docker)
sail up -d              # 啟動
sail down               # 停止
sail artisan migrate    # 執行遷移
sail npm run dev        # 前端開發模式

# 傳統環境
php artisan serve       # 啟動開發伺服器
npm run dev            # 編譯前端資源
```

## 專案結構
```
app/Http/Controllers/   # 控制器
resources/views/        # Blade 模板
resources/js/          # Vue 元件
public/                # 靜態資源
routes/web.php         # 路由定義
```

## 核心開發規範

### 路由與控制器
- 使用 Resource Controller: `Route::resource('orders', OrderController::class)`
- 控制器方法: index, create, store, show, edit, update, destroy

### 資料庫
- 遷移檔放 `database/migrations/`
- 模型放 `app/Models/`
- 關聯: `belongsTo`, `hasMany`, `belongsToMany`

### 前端整合
- Blade 中引入 Vite: `@vite(['resources/css/app.css', 'resources/js/app.js'])`
- Vue 掛載: `createApp(Component).mount('#app')`
- Bootstrap 已全域載入

### DataTables 設定
- 預設每頁 15 筆: `pageLength: 15`
- 繁體中文語言檔: `/js/zh-TW.json`
- 整合位置: `resources/js/app.js`

## 常見任務

### 新增功能模組
1. 建立遷移: `sail artisan make:migration create_xxx_table`
2. 建立模型: `sail artisan make:model Xxx`
3. 建立控制器: `sail artisan make:controller XxxController --resource`
4. 定義路由: `routes/web.php`
5. 建立視圖: `resources/views/xxx/`

### Excel 匯入處理
- 使用 `Maatwebsite\Excel`
- 注意 JSON 欄位需額外處理
- 範例: OrderController 的 import 方法

## 環境變數關鍵設定
```
APP_ENV=local
DB_CONNECTION=mysql
DB_HOST=mysql          # Sail 用 'mysql'，傳統用 '127.0.0.1'
DB_DATABASE=your_db
```

## 除錯技巧
- 查看日誌: `storage/logs/laravel.log`
- Dump & Die: `dd($variable)`
- 清除快取: `sail artisan optimize:clear`

## 注意事項
- Mac/Windows 路徑差異: 使用 `DIRECTORY_SEPARATOR`
- 資料庫連線: Sail 用容器名稱，非 localhost
- npm 套件問題: 刪除 `node_modules` 重裝

---
詳細文件另見 README.md
