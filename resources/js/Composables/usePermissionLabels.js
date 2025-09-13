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
    }

    const label = (permissionName) => map[permissionName] || permissionName

    return { label }
}

