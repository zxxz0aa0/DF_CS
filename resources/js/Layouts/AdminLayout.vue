<template>
    <div class="app-wrapper">
        <!-- 導航列 -->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <!-- 左側：側邊欄切換 -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="sidebar" role="button" @click.prevent="toggleSidebar">
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
                            <template v-if="hasRole('admin')">
                                <Link :href="route('dashboard')" class="dropdown-item">
                                    <i class="bi bi-house me-2"></i>前台
                                </Link>
                                <Link :href="route('admin.dashboard')" class="dropdown-item">
                                    <i class="bi bi-speedometer2 me-2"></i>後台
                                </Link>
                                <div class="dropdown-divider"></div>
                            </template>
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
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
                        <!-- 儀表板 -->
                        <li class="nav-item">
                            <Link :href="route('admin.dashboard')" class="nav-link" :class="{ active: route().current('admin.dashboard') }">
                                <i class="nav-icon bi bi-speedometer2"></i>
                                <p>儀表板</p>
                            </Link>
                        </li>

                        <!-- 使用者管理 -->
                        <li v-if="canSeeUserManagement" class="nav-item" :class="{ 'menu-open': isUserManagementActive || userMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isUserManagementActive }" @click.prevent="toggleUserMenu">
                                <i class="nav-icon bi bi-people"></i>
                                <p>
                                    使用者管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <Link :href="route('admin.users.index')" class="nav-link" :class="{ active: route().current('admin.users.*') }">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>所有使用者</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>

                        <!-- 權限管理 -->
                        <li v-if="canSeeRolePermission" class="nav-item" :class="{ 'menu-open': isPermissionManagementActive || permissionMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isPermissionManagementActive }" @click.prevent="togglePermissionMenu">
                                <i class="nav-icon bi bi-shield-lock"></i>
                                <p>
                                    權限管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <Link :href="route('admin.roles.index')" class="nav-link" :class="{ active: route().current('admin.roles.*') }">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>角色管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item">
                                    <Link :href="route('admin.permissions.index')" class="nav-link" :class="{ active: route().current('admin.permissions.*') }">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>權限管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>

                        <!-- 公司管理 -->
                        <li v-if="canSeeCompanyManagement" class="nav-item" :class="{ 'menu-open': isCompanyManagementActive || companyMenuOpen }">
                            <a href="#" class="nav-link" :class="{ active: isCompanyManagementActive }" @click.prevent="toggleCompanyMenu">
                                <i class="nav-icon bi bi-buildings"></i>
                                <p>
                                    公司管理
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <Link :href="route('admin.company-categories.index')" class="nav-link" :class="{ active: route().current('admin.company-categories.*') }">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>公司類別管理</p>
                                    </Link>
                                </li>
                                <li class="nav-item">
                                    <Link :href="route('admin.companies.index')" class="nav-link" :class="{ active: route().current('admin.companies.*') }">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>公司資料管理</p>
                                    </Link>
                                </li>
                            </ul>
                        </li>

                        <!-- 系統設定 -->
                        <li class="nav-header">系統設定</li>
                        <li class="nav-item">
                            <Link :href="route('dashboard')" class="nav-link">
                                <i class="nav-icon bi bi-house"></i>
                                <p>回到前台</p>
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- 主內容區 -->
        <main class="app-main">
            <!-- 內容標題 -->
            <div class="app-content-header" v-if="$slots.header">
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
            <strong>版權所有 &copy; {{ new Date().getFullYear() }} DF管理系統</strong>
            保留所有權利。
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

// 取得前端共享的角色/權限（在 <script setup> 需用 usePage 取得 $page）
const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const perms = computed(() => page.props.auth?.permissions ?? [])
const hasRole = (name) => roles.value?.includes?.(name)
const can = (name) => perms.value?.includes?.(name)

// 控制側欄顯示：
// - 只有 admin 或具備相關權限者才看得到「使用者管理」「權限管理」
const canSeeUserManagement = computed(() => {
    return hasRole('admin') ||
        can('view users') || can('create users') || can('edit users') || can('delete users')
})

const canSeeRolePermission = computed(() => {
    return hasRole('admin') || can('manage roles')
})

const canSeeCompanyManagement = computed(() => {
    // 目前公司管理路由仍僅開放 admin 使用
    return hasRole('admin')
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

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value
    if (userMenuOpen.value) {
        permissionMenuOpen.value = false
        companyMenuOpen.value = false
    }
}

const togglePermissionMenu = () => {
    permissionMenuOpen.value = !permissionMenuOpen.value
    if (permissionMenuOpen.value) {
        userMenuOpen.value = false
        companyMenuOpen.value = false
    }
}

const toggleCompanyMenu = () => {
    companyMenuOpen.value = !companyMenuOpen.value
    if (companyMenuOpen.value) {
        userMenuOpen.value = false
        permissionMenuOpen.value = false
    }
}

// 設定 AdminLTE v4 需要的 body 類名
// 依 AdminLTE v4 範例：預設展開側欄於桌面，行動裝置使用 overlay
// 這裡不啟用 mini 模式，避免與收合互相干擾
const bodyClasses = ['layout-fixed', 'sidebar-expand-lg', 'sidebar-collapse']

onMounted(() => {
    document.body.classList.add(...bodyClasses)
    // 僅在後台載入 AdminLTE 的資源（CSS/JS），避免影響前台
    import('../../css/admin.css')
    import('admin-lte/dist/js/adminlte.min.js')
})

onBeforeUnmount(() => {
    document.body.classList.remove(...bodyClasses)
})

const toggleSidebar = () => {
    const isLgUp = window.matchMedia('(min-width: 992px)').matches
    if (isLgUp) {
        document.body.classList.toggle('sidebar-collapse')
    } else {
        document.body.classList.toggle('sidebar-open')
    }
}

const closeSidebar = () => {
    document.body.classList.remove('sidebar-open')
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
