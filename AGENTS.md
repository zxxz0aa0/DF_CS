# AGENTS

## 目的
This document is provided for use in this Laravel/Web project. The common operating principles for AI assistants and engineers prioritize ensuring stability, maintainability, and clear specifications.The English writing section of the AGENTS.md file does not need to be changed to Traditional Chinese.

## 使命與範圍
- Mission: To assist in bug fixing, feature development, documentation writing, and code review to maintain system stability and maintainability.
- Scope: Changes are limited to those required by the project; avoid unauthorized alterations to the architecture or large amounts of files.

## 任務類型指引（Task Types）

- Bug：
  - 先定位問題來源與重現方式
  - 最小修改修正，不順便重構
  - 修正後說明風險與測試狀況

- Feature：
  - 先確認需求邊界與資料流
  - 不影響既有功能為優先
  - 若涉及資料表或流程變更，需事前說明

- Review：
  - 只指出問題與改善建議
  - 不直接修改程式碼
  - 區分「必修」與「建議」

## 技術堆疊 (Tech Stack)
- Backend: PHP 8.2、Laravel 11.x、Sanctum、Inertia (laravel/inertiajs)、Ziggy、Spatie Laravel-Permission、Maatwebsite Excel。
- Frontend: Vue 3 + Inertia、Vite、Tailwind CSS、Bootstrap 5 + AdminLTE、Bootstrap Icons、DataTables (datatables.net + datatables.net-bs5 + datatables.net-vue3)、Axios。
- Dev/Test: Laravel Breeze、Sail、Pint、Pail、PHPUnit 11、Mockery、Faker、Laravel Vite Plugin、PostCSS/Autoprefixer、concurrently。

## 實作前
- 實作前務必確認並取得同意：**要做什麼**、**怎麼做**、**會改哪些檔案**。
- 若範圍不清楚或影響多處，先行確認與核准後再動手。

## 基本原則
- 嚴格遵循 Laravel 既有慣例、目錄結構與命名方式。
- 偏好小步快走，避免未經同意的大型重構或廣泛多檔修改。
- 以穩定與可維護為優先，避免過度炫技或過度抽象。
- 說明與註解使用繁體中文簡短說明;每段程式碼開始前一行都寫入說明做什麼用；技術標識（如 class/method 名）可用 English 以節省 Token。
- 當有假設或決策時，以繁體中文簡述記錄。

## 工作方式
- 將工作拆成可審查的小步驟，逐步提交。
- 提案時列出預計修改的檔案，避免未經確認新增修改範圍。
- 若範疇擴大，立即暫停並再行請示。
- 未經要求勿改變現有行為；優先善用框架內建能力而非自訂抽象。

## 程式與註解
- 程式風格需符合現有 Laravel 風格（controllers/requests/resources/models/migrations/routes/views/tests）。
- 函式與類別保持聚焦、避免過早抽象。
- 註解只說明意圖或非直覺決策，使用繁體中文，避免冗餘敘事。

## 測試與驗證
- 適當時以小步方式補齊或更新測試。
- 只跑與變更相關的目標測試，非必要避免全套重度測試。
- 每次變更後點出已知限制或風險。

## 審查與核准
- 未經明確核准，不得進行大型重構或跨域修改。
- 若需多檔或跨模組變更，先提供簡短計畫並等待確認。
- 尊重審查意見，以繁體中文回覆精簡更新。

## 範圍控管
- 未經討論與核准，不新增額外相依套件。
- 資料庫與 Schema 變更需謹慎、保持最小化，並說明 migration 影響。
- 未經指示，不修改部署、CI/CD、環境設定。

## 溝通
- 預設使用繁體中文解說，技術識別用 English 以便精簡。
- 每次工作後清楚摘要：改了什麼、為何改、後續事項。
- 若遇阻或不確定，及早提問再前進。
