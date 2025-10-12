<template>
    <AdminLayout :user="$page.props.auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                文件管理
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-12xl mx-auto sm:px-2 lg:px-0">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- 統計卡片區 -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">總文件數</h5>
                                        <h2 class="mb-0">{{ stats.total }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">即將到期</h5>
                                        <h2 class="mb-0">{{ stats.expiring_soon }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">已過期</h5>
                                        <h2 class="mb-0">{{ stats.expired }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 功能按鈕區 -->
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex space-x-2">
                                <Link
                                    v-if="can('create documents')"
                                    :href="route('admin.documents.create')"
                                    class="btn btn-primary"
                                >
                                    <i class="bi bi-plus-lg me-1"></i>
                                    新增文件
                                </Link>
                            </div>

                            <!-- 搜尋區 -->
                            <div class="flex space-x-2">
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    placeholder="搜尋文件名稱或文件號碼..."
                                    class="form-control"
                                    style="width: 250px;"
                                    @keyup.enter="search"
                                >
                                <select v-model="searchForm.category" class="form-select" style="width: 150px;">
                                    <option value="">所有類別</option>
                                    <option value="identity">身分證件</option>
                                    <option value="insurance">保險證件</option>
                                    <option value="vehicle">車輛證件</option>
                                </select>
                                <select v-model="searchForm.status" class="form-select" style="width: 150px;">
                                    <option value="">所有狀態</option>
                                    <option value="valid">有效</option>
                                    <option value="expiring_soon">即將到期</option>
                                    <option value="expired">已過期</option>
                                </select>
                                <button @click="search" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button @click="clearSearch" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 資料表格 -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>文件名稱</th>
                                        <th>類別</th>
                                        <th>關聯對象</th>
                                        <th>備註</th>
                                        <th>到期日</th>
                                        <th>狀態</th>
                                        <th>檔案數</th>
                                        <th width="150">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="document in documents.data" :key="document.id">
                                        <td>
                                            <strong>{{ document.document_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ document.category_text }}</span>
                                        </td>
                                        <td>
                                            <span v-if="document.driver">
                                                <i class="bi bi-person me-1"></i>{{ document.driver.name }}
                                            </span>
                                            <span v-if="document.vehicle">
                                                <i class="bi bi-car-front me-1"></i>{{ document.vehicle.license_number }}
                                            </span>
                                        </td>
                                        <td>
                                            <span v-if="document.notes">{{ document.notes }}</span>
                                            <span v-else class="text-muted">—</span>
                                        </td>
                                        <td>
                                            <div v-if="document.expiry_date">
                                                <div>{{ formatDate(document.expiry_date) }}
                                                <small :class="getDaysClass(document.days_until_expiry)">
                                                    ({{ getDaysText(document.days_until_expiry) }})
                                                </small>
                                                </div>
                                            </div>
                                            <span v-else class="text-muted">—</span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="`bg-${document.status_color}`">
                                                {{ document.status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ document.files.length }} 個檔案
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <Link
                                                    v-if="can('view documents')"
                                                    :href="route('admin.documents.show', document.id)"
                                                    class="btn btn-outline-info"
                                                    title="查看"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </Link>
                                                <Link
                                                    v-if="can('edit documents')"
                                                    :href="route('admin.documents.edit', document.id)"
                                                    class="btn btn-outline-primary"
                                                    title="編輯"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    v-if="can('delete documents')"
                                                    @click="confirmDelete(document)"
                                                    class="btn btn-outline-danger"
                                                    title="刪除"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 如果沒有資料 -->
                        <div v-if="documents.data.length === 0" class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">目前沒有文件記錄</p>
                        </div>

                        <!-- 分頁 -->
                        <nav v-if="documents.last_page > 1" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: !documents.prev_page_url }">
                                    <Link
                                        :href="documents.prev_page_url || '#'"
                                        class="page-link"
                                        :data="{ ...searchForm }"
                                        preserve-scroll
                                    >
                                        上一頁
                                    </Link>
                                </li>
                                <li
                                    v-for="page in getPageNumbers()"
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: page === documents.current_page }"
                                >
                                    <Link
                                        :href="getPageUrl(page)"
                                        class="page-link"
                                        :data="{ ...searchForm }"
                                        preserve-scroll
                                    >
                                        {{ page }}
                                    </Link>
                                </li>
                                <li class="page-item" :class="{ disabled: !documents.next_page_url }">
                                    <Link
                                        :href="documents.next_page_url || '#'"
                                        class="page-link"
                                        :data="{ ...searchForm }"
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

        <!-- 刪除確認 Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認刪除文件</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>確定要刪除以下文件嗎？</p>
                        <div v-if="documentToDelete" class="alert alert-warning">
                            <strong>文件名稱：</strong>{{ documentToDelete.document_name }}<br>
                            <strong>類別：</strong>{{ documentToDelete.category_text }}<br>
                            <strong>檔案數量：</strong>{{ documentToDelete.files.length }} 個
                        </div>
                        <p class="text-danger">此操作將刪除文件及所有關聯的檔案，且無法復原！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-danger" @click="deleteDocument">確認刪除</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    documents: Object,
    filters: Object,
    stats: Object,
})

const page = usePage()
const searchForm = reactive({
    search: props.filters.search || '',
    category: props.filters.category || '',
    status: props.filters.status || '',
})

const documentToDelete = ref(null)
let deleteModal = null

const can = (permission) => {
    return page.props.auth.permissions.includes(permission)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('zh-TW')
}

const getDaysText = (days) => {
    if (days === null) return ''
    if (days < 0) return `已過期 ${Math.abs(days)} 天`
    return `剩餘 ${days} 天`
}

const getDaysClass = (days) => {
    if (days === null) return 'text-muted'
    if (days < 0) return 'text-danger fw-bold'
    if (days <= 60) return 'text-primary fw-bold'
    return 'text-success'
}

const search = () => {
    router.get(route('admin.documents.index'), searchForm, {
        preserveState: true,
        replace: true,
    })
}

const clearSearch = () => {
    searchForm.search = ''
    searchForm.category = ''
    searchForm.status = ''
    search()
}

const confirmDelete = (document) => {
    documentToDelete.value = document
    deleteModal.show()
}

const deleteDocument = () => {
    if (documentToDelete.value) {
        router.delete(route('admin.documents.destroy', documentToDelete.value.id), {
            onSuccess: () => {
                deleteModal.hide()
                documentToDelete.value = null
            }
        })
    }
}

const getPageNumbers = () => {
    const current = props.documents.current_page
    const last = props.documents.last_page
    const pages = []

    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
    }

    return pages
}

const getPageUrl = (page) => {
    return route('admin.documents.index', { ...searchForm, page })
}

onMounted(() => {
    setTimeout(() => {
        const modalElement = document.getElementById('deleteModal')
        if (modalElement && window.bootstrap) {
            deleteModal = new window.bootstrap.Modal(modalElement)
        }
    }, 100)
})
</script>
