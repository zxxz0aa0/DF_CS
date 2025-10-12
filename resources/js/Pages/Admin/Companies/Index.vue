<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">公司資料管理</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('admin.dashboard')">管理後台</Link>
                        </li>
                        <li class="breadcrumb-item active">公司資料管理</li>
                    </ol>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background-color:#B3D9D9;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">公司列表</h3>
                            <Link :href="route('admin.companies.create')" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>新增公司
                            </Link>
                        </div>
                    </div>

                    <div class="card-body">
                        <div v-if="flash.success" class="alert alert-success mb-3">
                            {{ flash.success }}
                        </div>
                        <div v-if="flash.error" class="alert alert-danger mb-3">
                            {{ flash.error }}
                        </div>
                        <!-- 搜尋功能 -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="搜尋公司名稱、統編或負責人..."
                                        v-model="searchQuery"
                                        @keyup.enter="search"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" @click="search">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" v-model="categoryFilter" @change="filterByCategory">
                                    <option value="">所有類別</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" v-model="statusFilter" @change="filterByStatus">
                                    <option value="">所有狀態</option>
                                    <option value="active">啟用</option>
                                    <option value="inactive">停用</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100" @click="clearFilters" v-if="hasFilters">
                                    清除篩選
                                </button>
                            </div>
                        </div>

                        <!-- 資料表格 -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>公司名稱</th>
                                        <th>類別</th>
                                        <th>統一編號</th>
                                        <th>負責人</th>
                                        <th>狀態</th>
                                        <th>建立時間</th>
                                        <th width="150">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="company in companies.data" :key="company.id">
                                        <td>{{ company.name }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ company.category?.name || '-' }}</span>
                                        </td>
                                        <td>{{ company.tax_id || '-' }}</td>
                                        <td>{{ company.representative || '-' }}</td>
                                        <td>
                                            <span
                                                class="badge"
                                                :class="company.status === 'active' ? 'bg-success' : 'bg-danger'"
                                            >
                                                {{ company.status === 'active' ? '啟用' : '停用' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(company.created_at) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link
                                                    :href="route('admin.companies.show', company.id)"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="檢視"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </Link>
                                                <Link
                                                    :href="route('admin.companies.edit', company.id)"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="編輯"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    @click="confirmDelete(company)"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="刪除"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="companies.data.length === 0">
                                        <td colspan="7" class="text-center text-muted py-4">
                                            暫無資料
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 分頁 -->
                        <div class="d-flex justify-content-between align-items-center mt-3" v-if="companies.last_page > 1">
                            <div class="text-muted">
                                顯示第 {{ companies.from || 0 }} 到 {{ companies.to || 0 }} 項，共 {{ companies.total }} 項
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item" :class="{ disabled: !companies.prev_page_url }">
                                        <Link
                                            class="page-link"
                                            :href="companies.prev_page_url"
                                            preserve-scroll
                                        >
                                            上一頁
                                        </Link>
                                    </li>
                                    <li
                                        class="page-item"
                                        :class="{ active: page === companies.current_page }"
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
                                    <li class="page-item" :class="{ disabled: !companies.next_page_url }">
                                        <Link
                                            class="page-link"
                                            :href="companies.next_page_url"
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
                        <p>您確定要刪除公司「<strong>{{ deleteTarget?.name }}</strong>」嗎？</p>
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
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    companies: Object,
    categories: Array,
    filters: Object,
})

const page = usePage()
const flash = computed(() => page.props.flash || {})

const searchQuery = ref(props.filters.search || '')
const categoryFilter = ref(props.filters.category_id || '')
const statusFilter = ref(props.filters.status || '')
const deleteTarget = ref(null)
const pendingDelete = ref(false)

const hasFilters = computed(() => {
    return searchQuery.value || categoryFilter.value || statusFilter.value
})

const search = () => {
    router.get(route('admin.companies.index'), {
        search: searchQuery.value,
        category_id: categoryFilter.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    })
}

const filterByCategory = () => {
    router.get(route('admin.companies.index'), {
        search: searchQuery.value,
        category_id: categoryFilter.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    })
}

const filterByStatus = () => {
    router.get(route('admin.companies.index'), {
        search: searchQuery.value,
        category_id: categoryFilter.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    searchQuery.value = ''
    categoryFilter.value = ''
    statusFilter.value = ''
    router.get(route('admin.companies.index'), {}, {
        preserveState: true,
        replace: true
    })
}

const confirmDelete = (company) => {
    deleteTarget.value = company
    pendingDelete.value = false
    const el = document.getElementById('deleteModal')
    if (el && window.bootstrap) {
        el.addEventListener('hidden.bs.modal', onDeleteModalHidden, { once: true })
        const modal = new window.bootstrap.Modal(el)
        modal.show()
    }
}

const cleanupModalArtifacts = () => {
    document.body.classList.remove('modal-open')
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove())
}
const performDeleteCompany = () => {
    router.delete(route('admin.companies.destroy', deleteTarget.value.id), {
        preserveScroll: true,
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
        performDeleteCompany()
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
    const current = props.companies.current_page
    const last = props.companies.last_page
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
    return route('admin.companies.index') + '?' + params.toString()
}
</script>
