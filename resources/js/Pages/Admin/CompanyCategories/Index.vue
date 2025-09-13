<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">公司類別管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">管理後台</Link>
                        </li>
                        <li class="breadcrumb-item active">公司類別管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">公司類別列表</h3>
                            <Link 
                                v-if="can('create company categories')"
                                :href="route('admin.company-categories.create')" 
                                class="btn btn-primary"
                            >
                                <i class="bi bi-plus-circle me-1"></i>新增類別
                            </Link>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- 搜尋功能 -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="搜尋類別名稱或描述..."
                                        v-model="searchQuery"
                                        @keyup.enter="search"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" @click="search">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100" @click="clearSearch" v-if="filters.search">
                                    清除搜尋
                                </button>
                            </div>
                        </div>

                        <!-- 資料表格 -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>類別名稱</th>
                                        <th>描述</th>
                                        <th>公司數量</th>
                                        <th>建立時間</th>
                                        <th width="120">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="category in categories.data" :key="category.id">
                                        <td>{{ category.name }}</td>
                                        <td>{{ category.description || '-' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ category.companies_count }}</span>
                                        </td>
                                        <td>{{ formatDate(category.created_at) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link
                                                    v-if="can('edit company categories')"
                                                    :href="route('admin.company-categories.edit', category.id)"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="編輯"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    v-if="can('delete company categories')"
                                                    @click="confirmDelete(category)"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="刪除"
                                                    :disabled="category.companies_count > 0"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="categories.data.length === 0">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            暫無資料
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 分頁 -->
                        <div class="d-flex justify-content-between align-items-center mt-3" v-if="categories.last_page > 1">
                            <div class="text-muted">
                                顯示第 {{ categories.from || 0 }} 到 {{ categories.to || 0 }} 項，共 {{ categories.total }} 項
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item" :class="{ disabled: !categories.prev_page_url }">
                                        <Link
                                            class="page-link"
                                            :href="categories.prev_page_url"
                                            preserve-scroll
                                        >
                                            上一頁
                                        </Link>
                                    </li>
                                    <li
                                        class="page-item"
                                        :class="{ active: page === categories.current_page }"
                                        v-for="page in getPageNumbers()"
                                        :key="page"
                                    >
                                        <Link
                                            class="page-link"
                                            :href="getPageUrl(page)"
                                            preserve-scroll
                                        >
                                            {{ page }}
                                        </Link>
                                    </li>
                                    <li class="page-item" :class="{ disabled: !categories.next_page_url }">
                                        <Link
                                            class="page-link"
                                            :href="categories.next_page_url"
                                            preserve-scroll
                                        >
                                            下一頁
                                        </Link>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>您確定要刪除類別「<strong>{{ deleteTarget?.name }}</strong>」嗎？</p>
                        <p class="text-danger small mb-0">此操作無法復原。</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="markPendingDelete">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { usePermissions } from '@/Composables/usePermissions'

const props = defineProps({
    categories: Object,
    filters: Object,
})

// 權限檢查
const { can } = usePermissions()

const searchQuery = ref(props.filters.search || '')
const deleteTarget = ref(null)
let deleteModal = null
let deleteModalEl = null
const pendingDelete = ref(false)

onMounted(() => {
    // 初始化 Bootstrap Modal 並監聽關閉事件
    deleteModalEl = document.getElementById('deleteModal')
    if (deleteModalEl && window.bootstrap) {
        deleteModal = new window.bootstrap.Modal(deleteModalEl)
        deleteModalEl.addEventListener('hidden.bs.modal', onDeleteModalHidden)
    }
})

onBeforeUnmount(() => {
    if (deleteModalEl) {
        deleteModalEl.removeEventListener('hidden.bs.modal', onDeleteModalHidden)
    }
})

const search = () => {
    router.get(route('admin.company-categories.index'), {
        search: searchQuery.value
    }, {
        preserveState: true,
        replace: true
    })
}

const clearSearch = () => {
    searchQuery.value = ''
    router.get(route('admin.company-categories.index'), {}, {
        preserveState: true,
        replace: true
    })
}

const confirmDelete = (category) => {
    deleteTarget.value = category
    pendingDelete.value = false
    const el = document.getElementById('deleteModal')
    if (el && window.bootstrap) {
        el.addEventListener('hidden.bs.modal', onDeleteModalHidden, { once: true })
        const modal = new window.bootstrap.Modal(el)
        modal.show()
    }
}

// 確保在發送請求前先關閉 Modal，避免 Inertia 導航時留下 backdrop
const cleanupModalArtifacts = () => {
    document.body.classList.remove('modal-open')
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove())
}
const performDeleteCategory = () => {
    router.delete(route('admin.company-categories.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            // 局部重新抓取當前頁面的 props，保留搜尋/分頁等狀態
            router.reload({ only: ['categories'], preserveScroll: true })
        },
        onFinish: () => {
            cleanupModalArtifacts()
            deleteTarget.value = null
            pendingDelete.value = false
        }
    })
}

const onDeleteModalHidden = () => {
    cleanupModalArtifacts()
    if (pendingDelete.value && deleteTarget.value) {
        performDeleteCategory()
    } else {
        pendingDelete.value = false
    }
}

const markPendingDelete = () => {
    pendingDelete.value = true
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    })
}

const getPageNumbers = () => {
    const current = props.categories.current_page
    const last = props.categories.last_page
    const pages = []
    
    let start = Math.max(1, current - 2)
    let end = Math.min(last, current + 2)
    
    for (let i = start; i <= end; i++) {
        pages.push(i)
    }
    
    return pages
}

const getPageUrl = (page) => {
    const params = new URLSearchParams(window.location.search)
    params.set('page', page)
    return route('admin.company-categories.index') + '?' + params.toString()
}
</script>
