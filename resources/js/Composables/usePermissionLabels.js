export function usePermissionLabels() {
    const map = {
        // 使用者管理
        'view users': '檢視使用者',
        'create users': '建立使用者',
        'edit users': '編輯使用者',
        'delete users': '刪除使用者',

        // 角色與權限管理 / 職務
        'manage roles': '管理角色與權限',
        'view positions': '檢視職務',
        'create positions': '建立職務',
        'edit positions': '編輯職務',
        'delete positions': '刪除職務',

        // 儀表板
        'view admin dashboard': '存取後台儀表板',

        // 公司管理
        'view companies': '檢視公司',
        'create companies': '建立公司',
        'edit companies': '編輯公司',
        'delete companies': '刪除公司',
        'view company categories': '檢視公司類別',
        'create company categories': '建立公司類別',
        'edit company categories': '編輯公司類別',
        'delete company categories': '刪除公司類別',

        // 駕駛管理
        'view drivers': '檢視駕駛',
        'create drivers': '建立駕駛',
        'edit drivers': '編輯駕駛',
        'delete drivers': '刪除駕駛',
        'export drivers': '匯出駕駛',
        'import drivers': '匯入駕駛',
        'view expiring licenses': '檢視到期證照',

        // 車輛管理
        'view vehicles': '檢視車輛',
        'create vehicles': '新增車輛',
        'edit vehicles': '編輯車輛',
        'delete vehicles': '刪除車輛',
        'export vehicles': '匯出車輛',
        'import vehicles': '匯入車輛',
        'manage vehicle status': '管理車輛狀態',
        'manage vehicle configs': '管理車輛配置',
        'view expiring inspections': '檢視到期檢驗',

        // 車輛牌照管理
        'view vehicle licenses': '檢視車輛牌照',
        'create vehicle licenses': '新增車輛牌照',
        'edit vehicle licenses': '編輯車輛牌照',
        'delete vehicle licenses': '刪除車輛牌照',
        'revoke vehicle licenses': '撤銷車輛牌照',
        'transfer vehicle licenses': '轉讓車輛牌照',
        'import vehicle licenses': '匯入車輛牌照',
        'export vehicle licenses': '匯出車輛牌照',
        'view vehicle license audit logs': '檢視牌照異動紀錄',

        // 報表
        'view reports': '檢視報表',
        'create reports': '建立報表',
        'export reports': '匯出報表',

        // 系統設定
        'manage system settings': '管理系統設定',
        'view system logs': '檢視系統日誌',
        'backup database': '資料庫備份',
        'restore database': '資料庫還原',

        // 財務
        'view financial reports': '檢視財務報表',
        'manage billing': '管理帳務',
        'view statistics': '檢視統計',

        // 會計科目管理
        'view accounts': '檢視會計科目',
        'create accounts': '新增會計科目',
        'edit accounts': '編輯會計科目',
        'delete accounts': '刪除會計科目',
        'manage accounts': '管理會計科目',
        'manage account categories': '管理會計類別',
        'import accounts': '匯入會計科目',
        'export accounts': '匯出會計科目',
        'view account audit logs': '檢視會計科目異動紀錄',

        // 帳務管理
        'view accounting': '檢視帳務',
        'create accounting': '新增帳務',
        'edit accounting': '編輯帳務',
        'delete accounting': '刪除帳務',
        'manage accounting': '管理帳務',
        'export accounting': '匯出帳務',

        // 支出款項管理
        'view expense payments': '檢視支出款項',
        'create expense payments': '新增支出款項',
        'edit expense payments': '編輯支出款項',
        'delete expense payments': '刪除支出款項',
        'import expense payments': '匯入支出款項',
        'export expense payments': '匯出支出款項',

        // 廠商管理
        'view vendors': '檢視廠商',
        'create vendors': '新增廠商',
        'edit vendors': '編輯廠商',
        'delete vendors': '刪除廠商',
        'export vendors': '匯出廠商',

        // 駕駛車輛綁定管理
        'view driver vehicle assignments': '檢視駕駛車輛綁定',
        'create driver vehicle assignments': '新增駕駛車輛綁定',
        'edit driver vehicle assignments': '編輯駕駛車輛綁定',
        'delete driver vehicle assignments': '刪除駕駛車輛綁定',
        'batch driver vehicle assignments': '批次綁定操作',
    }

    // 權限分組名稱對照表
    const groupLabels = {
        'users': '使用者管理',
        'roles': '角色管理',
        'positions': '職務管理',
        'companies': '公司管理',
        'categories': '類別管理',
        'drivers': '駕駛管理',
        'licenses': '證照管理',
        'logs': '日誌管理',
        'dashboard': '儀表板',
        'reports': '報表管理',
        'settings': '系統設定',
        'database': '資料庫',
        'billing': '帳務',
        'statistics': '統計',
        'vehicles': '車輛管理',
        'status': '狀態管理',
        'inspections': '檢驗管理',
        'configs': '配置管理',
        'vendors': '廠商管理',
        'assignments': '駕駛車輛綁定管理',
        'accounts': '會計科目管理',
        'accounting': '帳務管理',
        'payments': '支出款項管理',
    }

    const label = (permissionName) => map[permissionName] || permissionName
    const groupLabel = (groupName) => groupLabels[groupName] || groupName

    return { label, groupLabel }
}
