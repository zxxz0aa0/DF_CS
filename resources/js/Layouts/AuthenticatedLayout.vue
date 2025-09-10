<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarOpen = ref(false);

// 取得目前使用者角色，僅 admin 顯示「前台/後台」快速連結
const page = usePage();
const roles = computed(() => page.props.auth?.roles ?? []);
const hasRole = (name) => roles.value?.includes?.(name);

// 為了讓前台下拉選單風格與後台一致，動態載入 Bootstrap 與 Icons 的樣式
const cleanup = ref(() => {})

onMounted(() => {
    import('bootstrap/dist/css/bootstrap.min.css');
    import('bootstrap-icons/font/bootstrap-icons.css');
    const onKey = (e) => { if (e.key === 'Escape') sidebarOpen.value = false }
    window.addEventListener('keydown', onKey)
    cleanup.value = () => window.removeEventListener('keydown', onKey)
});

onBeforeUnmount(() => cleanup.value?.())

const toggleSidebar = () => { sidebarOpen.value = !sidebarOpen.value }
const closeSidebar = () => { sidebarOpen.value = false }
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-gray-50 bg-white relative z-50">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <button
                                @click="toggleSidebar"
                                class="me-2 inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:bg-gray-100 focus:text-gray-700 focus:outline-none"
                                aria-label="Toggle sidebar"
                            >
                                <i class="bi bi-list text-xl"></i>
                            </button>


                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-0 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    車行管理系統
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown (Bootstrap style to match Admin) -->
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-gray-700" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-circle me-1"></i>
                                        {{ $page.props.auth.user.name }}
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

                        <div class="-me-2 flex items-center sm:hidden" />
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
        <!-- Off-canvas sidebar & overlay -->
        <div class="fixed inset-0 z-40" aria-hidden="true" v-show="sidebarOpen">
            <div class="absolute inset-0 bg-black/30" @click="closeSidebar"></div>
        </div>
        <aside
            class="fixed top-0 left-0 z-50 h-full w-56 transform bg-white shadow-lg transition-transform duration-200"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            aria-label="Sidebar navigation"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <div class="font-semibold">功能選單</div>
                <button class="p-2 text-gray-500 hover:text-gray-700" @click="closeSidebar" aria-label="Close sidebar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <nav class="p-3 space-y-1">
                <Link :href="route('dashboard')" @click="closeSidebar" class="flex items-center rounded px-3 py-2 text-gray-700 hover:bg-gray-100">
                    <i class="bi bi-speedometer2 me-2"></i> 儀表板
                </Link>
                <Link :href="route('profile.edit')" @click="closeSidebar" class="flex items-center rounded px-3 py-2 text-gray-700 hover:bg-gray-100">
                    <i class="bi bi-person-gear me-2"></i> 個人設定
                </Link>
                <template v-if="hasRole('admin')">
                    <div class="mt-3 text-xs text-gray-400 px-3">管理</div>
                    <Link :href="route('admin.dashboard')" @click="closeSidebar" class="flex items-center rounded px-3 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="bi bi-shield-lock me-2"></i> 後台儀表板
                    </Link>
                </template>
            </nav>
        </aside>
    </div>
</template>
