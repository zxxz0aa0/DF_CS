<template>
    <div class="app-wrapper">
        <!-- 導航列 -->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <!-- 左側：側邊欄切換 -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#" role="button" @click.prevent="toggleSidebar">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>

                <!-- 右側導航 -->
                <ul class="navbar-nav ms-auto">
                    <!-- 使用者下拉選單 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ user.name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!--<template v-if="can('view admin dashboard')">
                                <Link :href="route('dashboard')" class="dropdown-item">
                                    <i class="bi bi-house me-2"></i>前台
                                </Link>
                                <Link :href="route('admin.dashboard')" class="dropdown-item">
                                    <i class="bi bi-speedometer2 me-2"></i>後台
                                </Link>
                                <div class="dropdown-divider"></div>
                            </template>-->
                            <Link :href="route('profile.edit')" class="dropdown-item">
                                <i class="bi bi-gear me-2"></i>個人設定
                            </Link>
                            <div class="dropdown-divider"></div>
                            <Link :href="route('logout')" method="post" as="button" class="dropdown-item">
                                <i class="bi bi-power me-2"></i>登出
                            </Link>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- 側邊欄 -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!-- 品牌標誌 -->
            <div class="sidebar-brand">
                <Link :href="route('admin.dashboard')" class="brand-link">
                    <span class="brand-text fw-bold ms-2">
                        {{ $page.props.app?.name || 'DF管理系統' }}
                    </span>
                </Link>
            </div>

            <!-- 側邊欄內容 -->
            <div class="sidebar-wrapper">

                <!-- 側邊欄選單 -->
                <nav class="mt-1">
                    <ul class="nav sidebar-menu flex-column" role="navigation" data-accordion="false">
                        <!-- 主要功能 -->
                        <li class="nav-header text-center text-white" style="background-color: #013A63;">主要功能</li>
                        <!-- 儀表板 -->
                        <li class="nav-item">
                            <Link :href="route('admin.dashboard')" class="nav-link" :class="{ active: route().current('admin.dashboard') }" @click="closeSidebar">
                                <i class="nav-icon bi bi-speedometer2"></i>
                                <p>儀表板</p>
                            </Link>
                        </li>

                        <!-- 帳務管理 -->
                        <li v-if="canSeeAccountingMenu" class="nav-item" :class="{ 'menu-open': isAccountingMenuActive || accountingMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isAccountingMenuActive }" data-lte-toggle="treeview" @click.prevent="toggleAccountingMenu">
                                <i class="nav-icon bi bi-calculator"></i>
                                <p>
                                    帳務管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li v-if="canSeeAccountingManagement" class="nav-item ps-4">
                                    <Link :href="route('admin.accounting.records.index')" class="nav-link" :class="{ active: route().current('admin.accounting.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-journal-text"></i>
                                        <p>帳務記錄</p>
                                    </Link>
                                </li>
                                <li v-if="canSeeExpensePaymentManagement" class="nav-item ps-4">
                                    <Link :href="route('admin.expense-payments.index')" class="nav-link" :class="{ active: isExpensePaymentActive }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-wallet2"></i>
                                        <p>支出款項管理</p>
                                    </Link>
                                </li>
                                <li v-if="canSeeCollectionManagement" class="nav-item ps-4">
                                    <Link :href="route('admin.collections.index')" class="nav-link" :class="{ active: route().current('admin.collections.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-exclamation-circle"></i>
                                        <p>催帳管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>

                        <!-- 駕駛管理 -->
                        <li v-if="canSeeDriverManagement" class="nav-item" :class="{ 'menu-open': isDriverManagementActive || driverMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isDriverManagementActive }" data-lte-toggle="treeview" @click.prevent="toggleDriverMenu">
                                <i class="nav-icon bi bi-person-vcard"></i>
                                <p>
                                    駕駛管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.drivers.index')" class="nav-link" :class="{ active: route().current('admin.drivers.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-person-lines-fill"></i>
                                        <p>駕駛資料管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <a :href="route('admin.drivers.expiring-licenses')" class="nav-link" @click="closeSidebar">
                                        <i class="nav-icon bi bi-check2-square"></i>
                                        <p>證照到期提醒</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- 車輛管理 -->
                        <li v-if="canSeeVehicleManagement" class="nav-item" :class="{ 'menu-open': isVehicleManagementActive || vehicleMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isVehicleManagementActive }" data-lte-toggle="treeview" @click.prevent="toggleVehicleMenu">
                                <i class="nav-icon bi bi-taxi-front-fill"></i>
                                <p>
                                    車輛管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.vehicles.index')" class="nav-link" :class="{ active: route().current('admin.vehicles.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-list-ul"></i>
                                        <p>車輛資料管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.vehicle-licenses.index')" class="nav-link" :class="{ active: route().current('admin.vehicle-licenses.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-tag-fill"></i>
                                        <p>牌照資料管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>

                        <!-- 駕駛車輛綁定管理 -->
                        <li v-if="canSeeAssignmentManagement" class="nav-item">
                            <Link :href="route('admin.driver-vehicle-assignments.index')" class="nav-link" :class="{ active: route().current('admin.driver-vehicle-assignments.*') }" @click="closeSidebar">
                                <i class="nav-icon bi bi-link-45deg"></i>
                                <p>駕駛車輛綁定</p>
                            </Link>
                        </li>


                        <!-- 文件管理 -->
                        <li v-if="canSeeDocumentManagement" class="nav-item">
                            <Link :href="route('admin.documents.index')" class="nav-link" :class="{ active: route().current('admin.documents.*') }" @click="closeSidebar">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>證件與保險管理</p>
                            </Link>
                        </li>

                        <!-- 廠商管理 -->
                        <li v-if="canSeeVendorManagement" class="nav-item">
                            <Link :href="route('admin.vendors.index')" class="nav-link" :class="{ active: route().current('admin.vendors.*') }" @click="closeSidebar">
                                <i class="nav-icon bi bi-shop"></i>
                                <p>廠商管理</p>
                            </Link>
                        </li>

                        <!-- 系統設定 -->
                        <li class="nav-header text-center text-white" style="background-color: #013A63;">系統設定</li>

                        <!-- 會計科目管理 -->
                        <li v-if="canSeeAccountManagement" class="nav-item" :class="{ 'menu-open': isAccountManagementActive || accountMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isAccountManagementActive }" data-lte-toggle="treeview" @click.prevent="toggleAccountMenu">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>
                                    會計科目管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.accounts.main-categories.index')" class="nav-link" :class="{ active: route().current('admin.accounts.main-categories.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-folder"></i>
                                        <p>總類管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.accounts.sub-categories.index')" class="nav-link" :class="{ active: route().current('admin.accounts.sub-categories.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-folder-plus"></i>
                                        <p>子分類管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.accounts.account.details.index')" class="nav-link" :class="{ active: route().current('admin.accounts.account.details.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-list-ul"></i>
                                        <p>科目明細管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>

                        <!-- 公司管理 -->
                        <li v-if="canSeeCompanyManagement" class="nav-item" :class="{ 'menu-open': isCompanyManagementActive || companyMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isCompanyManagementActive }" data-lte-toggle="treeview" @click.prevent="toggleCompanyMenu">
                                <i class="nav-icon bi bi-buildings"></i>
                                <p>
                                    公司管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.company-categories.index')" class="nav-link" :class="{ active: route().current('admin.company-categories.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-building"></i>
                                        <p>公司類別管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.companies.index')" class="nav-link" :class="{ active: route().current('admin.companies.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-building-add"></i>
                                        <p>公司資料管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <!-- 使用者管理 -->
                        <li v-if="canSeeUserManagement" class="nav-item" :class="{ 'menu-open': isUserManagementActive || userMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isUserManagementActive }" data-lte-toggle="treeview" @click.prevent="toggleUserMenu">
                                <i class="nav-icon bi bi-people"></i>
                                <p>
                                    使用者管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.users.index')" class="nav-link" :class="{ active: route().current('admin.users.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-person-fill-add"></i>
                                        <p>所有使用者</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li v-if="canSeeRolePermission" class="nav-item" :class="{ 'menu-open': isPermissionManagementActive || permissionMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isPermissionManagementActive }" data-lte-toggle="treeview" @click.prevent="togglePermissionMenu">
                                <i class="nav-icon bi bi-shield-lock"></i>
                                <p>
                                    權限管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.roles.index')" class="nav-link" :class="{ active: route().current('admin.roles.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-people"></i>
                                        <p>角色管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.permissions.index')" class="nav-link" :class="{ active: route().current('admin.permissions.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-key-fill"></i>
                                        <p>權限管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item ps-4">
                                    <Link :href="route('admin.positions.index')" class="nav-link" :class="{ active: route().current('admin.positions.*') }" @click="closeSidebar">
                                        <i class="nav-icon bi bi-person-vcard-fill"></i>
                                        <p>職務管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <!--<li class="nav-item">
                            <Link :href="route('dashboard')" class="nav-link">
                                <i class="nav-icon bi bi-house"></i>
                                <p>回到前台</p>
                            </Link>
                        </li>-->
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- 主內容區 -->
        <main class="app-main">
            <!-- 內容標題 -->
            <div class="app-content-header"  v-if="$slots.header">
                <div class="container-fluid">
                    <slot name="header" />
                </div>
            </div>

            <!-- 主要內容 -->
            <div class="app-content">
                <div class="container-fluid">
                    <slot />
                </div>
            </div>
        </main>

        <!-- 頁腳 -->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">
                <b>版本</b> 1.0.0
            </div>
            <strong>大豐交通企業 &copy; {{ new Date().getFullYear() }} 車行管理系統</strong>

        </footer>

        <!-- Overlay：行動裝置開啟側欄時點擊可關閉 -->
        <div class="sidebar-overlay" @click="closeSidebar"></div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

defineProps({
    user: {
        type: Object,
        required: true,
    },
})

const userMenuOpen = ref(false)
const permissionMenuOpen = ref(false)
const companyMenuOpen = ref(false)
const driverMenuOpen = ref(false)
const vehicleMenuOpen = ref(false)
const vehicleLicenseMenuOpen = ref(false)
const accountMenuOpen = ref(false)
const accountingMenuOpen = ref(false)
const sidebarOpen = ref(false)

// 取得前端共享的角色/權限（在 <script setup> 需用 usePage 取得 $page）
const page = usePage()
const perms = computed(() => {
    const authPerms = page.props.auth?.permissions
    return Array.isArray(authPerms) ? authPerms : []
})
const can = (name) => {
    if (!name || typeof name !== 'string') return false
    return perms.value.includes(name)
}

// 控制側欄顯示：僅依權限決定顯示（不因為 admin 角色而全部顯示）
const canSeeUserManagement = computed(() => {
    return can('view users') || can('create users') || can('edit users') || can('delete users')
})

const canSeeRolePermission = computed(() => {
    return can('manage roles') ||
        can('view positions') || can('create positions') || can('edit positions') || can('delete positions')
})

const canSeeCompanyManagement = computed(() => {
    return can('view companies') || can('create companies') || can('edit companies') || can('delete companies') ||
        can('view company categories') || can('create company categories') || can('edit company categories') || can('delete company categories')
})

const canSeeDriverManagement = computed(() => {
    return can('view drivers') || can('create drivers') || can('edit drivers') || can('delete drivers') ||
        can('export drivers') || can('import drivers') || can('view expiring licenses')
})

const canSeeVehicleManagement = computed(() => {
    return can('view vehicles') || can('create vehicles') || can('edit vehicles') || can('delete vehicles') || can('manage vehicle status')
})

const canSeeVehicleLicenseManagement = computed(() => {
    return can('view vehicle licenses') || can('create vehicle licenses') || can('edit vehicle licenses') || can('delete vehicle licenses')
})

const canSeeVendorManagement = computed(() => {
    return can('view vendors') || can('create vendors') || can('edit vendors') || can('delete vendors') || can('export vendors')
})

const canSeeDocumentManagement = computed(() => {
    return can('view documents') || can('create documents') || can('edit documents') || can('delete documents') || can('download documents')
})

const canSeeAssignmentManagement = computed(() => {
    return can('view driver vehicle assignments') || can('create driver vehicle assignments') ||
           can('edit driver vehicle assignments') || can('delete driver vehicle assignments')
})

const canSeeAccountManagement = computed(() => {
    return can('view accounts') || can('create accounts') || can('edit accounts') || can('delete accounts') || can('manage accounts')
})

const canSeeAccountingManagement = computed(() => {
    return can('view accounting') || can('create accounting') || can('edit accounting') || can('delete accounting') || can('export accounting')
})

const canSeeExpensePaymentManagement = computed(() => {
    return can('view expense payments') || can('create expense payments') || can('edit expense payments') ||
        can('delete expense payments') || can('export expense payments') || can('import expense payments')
})

const canSeeCollectionManagement = computed(() => {
    return can('view collections')
})

const canSeeAccountingMenu = computed(() => {
    return canSeeAccountingManagement.value || canSeeExpensePaymentManagement.value || canSeeCollectionManagement.value
})

const isUserManagementActive = computed(() => {
    return route().current('admin.users.*')
})

const isPermissionManagementActive = computed(() => {
    return route().current('admin.roles.*') || route().current('admin.permissions.*')
})

const isCompanyManagementActive = computed(() => {
    return route().current('admin.company-categories.*') || route().current('admin.companies.*')
})

const isDriverManagementActive = computed(() => {
    return route().current('admin.drivers.*')
})

const isVehicleManagementActive = computed(() => {
    return route().current('admin.vehicles.*')
})

const isVehicleLicenseManagementActive = computed(() => {
    return route().current('admin.vehicle-licenses.*')
})

const isAccountManagementActive = computed(() => {
    return route().current('admin.accounts.*')
})

const isExpensePaymentActive = computed(() => {
    return route().current('admin.expense-payments.*')
})

const isAccountingMenuActive = computed(() => {
    return route().current('admin.accounting.*') || isExpensePaymentActive.value
})

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value
    if (userMenuOpen.value) {
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const togglePermissionMenu = () => {
    permissionMenuOpen.value = !permissionMenuOpen.value
    if (permissionMenuOpen.value) {
        userMenuOpen.value = false
        companyMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const toggleCompanyMenu = () => {
    companyMenuOpen.value = !companyMenuOpen.value
    if (companyMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const toggleDriverMenu = () => {
    driverMenuOpen.value = !driverMenuOpen.value
    if (driverMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
        vehicleMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const toggleVehicleMenu = () => {
    vehicleMenuOpen.value = !vehicleMenuOpen.value
    if (vehicleMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const toggleVehicleLicenseMenu = () => {
    vehicleLicenseMenuOpen.value = !vehicleLicenseMenuOpen.value
    if (vehicleLicenseMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleMenuOpen.value = false
        accountMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const toggleAccountMenu = () => {
    accountMenuOpen.value = !accountMenuOpen.value
    if (accountMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountingMenuOpen.value = false
    }
}

const toggleAccountingMenu = () => {
    accountingMenuOpen.value = !accountingMenuOpen.value
    if (accountingMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
        driverMenuOpen.value = false
        vehicleMenuOpen.value = false
        vehicleLicenseMenuOpen.value = false
        accountMenuOpen.value = false
    }
}

// 設定 AdminLTE v4 需要的 body 類名
const bodyClasses = ['layout-fixed', 'sidebar-expand-lg']

// 處理窗口大小變化
const handleResize = () => {
    const isLargeScreen = window.innerWidth >= 992

    if (isLargeScreen) {
        // 大螢幕：移除小螢幕的 sidebar-open 狀態
        document.body.classList.remove('sidebar-open')
        // 根據當前狀態決定是否使用 sidebar-collapse
        if (!sidebarOpen.value) {
            document.body.classList.add('sidebar-collapse')
        }
    } else {
        // 小螢幕：移除大螢幕的 sidebar-collapse 狀態
        document.body.classList.remove('sidebar-collapse')
        // 根據當前狀態決定是否顯示側邊欄
        if (sidebarOpen.value) {
            document.body.classList.add('sidebar-open')
        }
    }
}

onMounted(async () => {
    document.body.classList.add(...bodyClasses)
    // 載入 CSS
    import('../../css/admin.css')

    // 載入 AdminLTE JS（只載入一次，讓其自動處理樹狀選單）
    if (!window.AdminLTE) {
        await import('admin-lte/dist/js/adminlte.min.js')
    }

    // 添加窗口大小變化監聽器
    window.addEventListener('resize', handleResize)

    // 初始化時執行一次
    handleResize()
})

onBeforeUnmount(() => {
    document.body.classList.remove(...bodyClasses)
    sidebarOpen.value = false

    // 移除事件監聽器
    window.removeEventListener('resize', handleResize)
})

const toggleSidebar = () => {
    // 檢查當前螢幕是否為大螢幕（桌面模式）
    const isLargeScreen = window.innerWidth >= 992 // Bootstrap lg 斷點

    if (isLargeScreen) {
        // 大螢幕：使用 sidebar-collapse 來控制收合
        const isCollapsed = document.body.classList.contains('sidebar-collapse')
        if (isCollapsed) {
            document.body.classList.remove('sidebar-collapse')
            sidebarOpen.value = true
        } else {
            document.body.classList.add('sidebar-collapse')
            sidebarOpen.value = false
        }
    } else {
        // 小螢幕：使用 sidebar-open 來控制顯示
        sidebarOpen.value = !sidebarOpen.value
        if (sidebarOpen.value) {
            document.body.classList.add('sidebar-open')
        } else {
            document.body.classList.remove('sidebar-open')
        }
    }
}

const closeSidebar = () => {
    sidebarOpen.value = false

    // 移除所有可能的側邊欄狀態類別
    document.body.classList.remove('sidebar-open')

    userMenuOpen.value = false
    permissionMenuOpen.value = false
    companyMenuOpen.value = false
    driverMenuOpen.value = false
    vehicleMenuOpen.value = false
    vehicleLicenseMenuOpen.value = false
    accountMenuOpen.value = false
    accountingMenuOpen.value = false

    // 在大螢幕上，如果需要完全隱藏側邊欄，可以加上 sidebar-collapse
    const isLargeScreen = window.innerWidth >= 992
    if (isLargeScreen) {
        document.body.classList.add('sidebar-collapse')
    }
}
</script>

<style scoped>
.dropdown-menu { margin-top: 0; }
.nav-link { cursor: pointer; }

/* 樹狀選單啟用項目顯示 */
.app-sidebar .nav-treeview > .nav-item > .nav-link.active {
    background-color: rgba(255,255,255,.9);
    color: #343a40;
}
</style>
