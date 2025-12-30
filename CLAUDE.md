# CLAUDE.md

此文件為 Claude Code (claude.ai/code) 在此代碼庫工作時提供指導。

## Claude 回答方式偏好
1. response in Traditional Chinese.
2. Before designing new features, plan ahead, discuss them thoroughly, and only execute after confirmation.
3. Focus on code security and quality
4. Create a todo list before writing or modifying code
5. When adding new code, add a brief Chinese comment at the beginning of each code segment.

# Vibe Coding – Programming Rules

## 思考優先於寫碼
- 在開始寫任何程式碼之前，**先用文字說明你的理解與解法**
- 明確說出：
  * 問題是什麼
  * 輸入 / 輸出是什麼
  * 邏輯流程（step by step）
- 若需求不完整，**必須先提出假設或詢問**

## 小步前進，避免一次寫太多
- 一次只完成一個明確功能
- 不要一次寫完整系統
- 若功能較大，請主動拆成多個階段（Phase / Step）

## 可讀性優先（Readable > Clever）
- 程式碼是給人看的，不是給電腦炫技
- 使用：
  * 明確的變數命名
  * 明確的函式名稱
- 避免：
  * 過度巢狀
  * 一行做太多事
  * 魔法數字（magic number）

## 不做過度設計（No Over-Engineering）
- 若目前需求不需要：
  * 不要先寫抽象層
  * 不要先寫泛用工具
  * 不要假設未來需求
  * 更改不相關的程式碼或是名稱
  * 要複述我的問題或重新解釋已經做過的功能
  * 不要給「完整教學文」，只給差異部分
- 優先選擇「**最簡單可行解**」

## 明確區分責任（Single Responsibility）
- 一個 function 只做一件事
- 一個 class 只負責一個角色
- Controller 不處理複雜商業邏輯
- 商業邏輯集中在 Service / Use Case

## 錯誤處理不可省略
- 所有可能失敗的地方都要：
  * 明確處理錯誤
  * 或清楚說明為何可忽略
- 錯誤訊息需「對人類友善」

## 與現有專案風格保持一致
- 遵守既有：
  * 命名規則
  * 資料夾結構
  * Coding Style
- 非必要不擅自引入新框架或新寫法

## 每段重要程式碼需附註解
- 簡單說明用途
- function前一行可以插入註解說明在做什麼

## 程式碼品質
- 避免 hard-code
- 邏輯盡量可獨立測試
- 若適合，請提出測試建議（但不強制寫測試）
- 要保持可讀性、可維護性、可擴展性

## 產出結束時必須回顧
- 簡要說明：
  - 這段程式碼解決了什麼
  - 還有哪些可改進但目前沒做的地方

## 技術棧
Laravel 10 + Vue 3 + Bootstrap 5 + MySQL 8(XAMPP)


